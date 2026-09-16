<?php

namespace App\Services\Localization;

use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Localization\AppTranslationBatchContract;
use App\Contracts\Localization\AppTranslationRepositoryContract;
use App\Contracts\Localization\SupportedLocalesContract;
use Throwable;

/**
 * SRP: translate the next batch of missing app strings and store them.
 *
 * A string whose English text is already translated elsewhere (another app,
 * same language) is copied instead of sent to Google again.
 */
final class AppTranslationBatchService implements AppTranslationBatchContract
{
    /** Stop a batch early when the translator keeps failing. */
    private const MAX_CONSECUTIVE_FAILURES = 3;

    /** Seconds of work per request, well under shared-hosting PHP limits. */
    private const TIME_BUDGET_SECONDS = 20;

    public function __construct(
        private readonly AppTranslationRepositoryContract $repository,
        private readonly SupportedLocalesContract $locales,
        private readonly TextTranslatorContract $translator,
    ) {}

    public function translateNextBatch(?string $app): array
    {
        $default = $this->locales->default();
        $targets = $this->locales->translatable();
        $batchSize = max(1, (int) config('localization.app_translate_batch_size', 40));

        $index = $this->repository->translatedIndex(null, $targets);
        $reusable = $this->reusableByEnglish($index);
        $pending = $this->pending($this->repository->englishRows($app, $default), $targets, $index);

        $translated = 0;
        $reused = 0;
        $failed = 0;
        $failuresInRow = 0;
        $done = 0;
        $startedAt = microtime(true);

        foreach (array_slice($pending, 0, $batchSize) as [$row, $locale]) {
            if (microtime(true) - $startedAt > self::TIME_BUDGET_SECONDS) {
                break;
            }

            $reuse = $reusable[$locale.'|'.$row['english']] ?? null;

            if ($reuse !== null) {
                $this->repository->saveTranslation($row['app'], $row['key'], $locale, $row['english'], $reuse);
                $reused++;
                $done++;

                continue;
            }

            try {
                $value = trim($this->translator->translate($row['english'], $default, $locale));
            } catch (Throwable) {
                $value = '';
            }

            if ($value === '') {
                $failed++;

                if (++$failuresInRow >= self::MAX_CONSECUTIVE_FAILURES) {
                    break;
                }

                continue;
            }

            $failuresInRow = 0;
            $this->repository->saveTranslation($row['app'], $row['key'], $locale, $row['english'], $value);
            $reusable[$locale.'|'.$row['english']] = $value;
            $translated++;
            $done++;
        }

        return [
            'translated' => $translated,
            'reused' => $reused,
            'failed' => $failed,
            'remaining' => max(0, count($pending) - $done),
        ];
    }

    /**
     * @param  array<int, array{app: string, key: string, english: string}>  $rows
     * @param  array<int, string>  $targets
     * @param  array<string, array{value: string, english: string}>  $index
     * @return array<int, array{0: array{app: string, key: string, english: string}, 1: string}>
     */
    private function pending(array $rows, array $targets, array $index): array
    {
        $pending = [];

        foreach ($rows as $row) {
            foreach ($targets as $locale) {
                if (! isset($index[$row['app'].'|'.$row['key'].'|'.$locale])) {
                    $pending[] = [$row, $locale];
                }
            }
        }

        return $pending;
    }

    /**
     * @param  array<string, array{value: string, english: string}>  $index
     * @return array<string, string> "locale|english" => translated value
     */
    private function reusableByEnglish(array $index): array
    {
        $map = [];

        foreach ($index as $compositeKey => $entry) {
            if ($entry['english'] === '') {
                continue;
            }

            $locale = substr($compositeKey, strrpos($compositeKey, '|') + 1);
            $map[$locale.'|'.$entry['english']] ??= $entry['value'];
        }

        return $map;
    }
}
