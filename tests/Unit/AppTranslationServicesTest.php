<?php

namespace Tests\Unit;

use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Localization\AppTranslationRepositoryContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Services\Localization\AppTranslationBatchService;
use App\Services\Localization\AppTranslationCatalogService;
use RuntimeException;
use Tests\TestCase;

class AppTranslationServicesTest extends TestCase
{
    public function test_register_skips_write_when_nothing_changed(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->english['dealer'] = ['cart.title' => 'Cart'];
        $service = new AppTranslationCatalogService($repository, new FakeLocales);

        $this->assertSame(1, $service->register('dealer', ['cart.title' => 'Cart']));
        $this->assertSame(0, $repository->saveEnglishCalls);
    }

    public function test_register_marks_keys_whose_english_changed(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->english['dealer'] = ['cart.title' => 'Cart'];
        $service = new AppTranslationCatalogService($repository, new FakeLocales);

        $service->register('dealer', ['cart.title' => 'My Cart', 'cart.empty' => 'Empty', 'bad' => '']);

        $this->assertSame(1, $repository->saveEnglishCalls);
        $this->assertSame(['cart.title'], $repository->lastChangedKeys);
        $this->assertSame(['cart.title' => 'My Cart', 'cart.empty' => 'Empty'], $repository->lastSavedItems);
    }

    public function test_batch_translates_missing_pairs_and_reuses_same_english(): void
    {
        config(['localization.app_translate_batch_size' => 10]);
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [
            ['app' => 'dealer', 'key' => 'common.cancel', 'english' => 'Cancel'],
            ['app' => 'customer', 'key' => 'common.cancel', 'english' => 'Cancel'],
        ];
        $translator = new FakeTranslator;

        $result = (new AppTranslationBatchService($repository, new FakeLocales, $translator))->translateNextBatch(null);

        // 2 rows x 2 languages: first of each language hits Google, second is reused.
        $this->assertSame(['translated' => 2, 'reused' => 2, 'failed' => 0, 'remaining' => 0], $result);
        $this->assertSame(2, $translator->calls);
        $this->assertSame('hi:Cancel', $repository->saved['customer|common.cancel|hi']);
    }

    public function test_batch_stops_after_repeated_translator_failures(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [
            ['app' => 'dealer', 'key' => 'a.one', 'english' => 'One'],
            ['app' => 'dealer', 'key' => 'a.two', 'english' => 'Two'],
            ['app' => 'dealer', 'key' => 'a.three', 'english' => 'Three'],
        ];
        $translator = new FakeTranslator;
        $translator->fail = true;

        $result = (new AppTranslationBatchService($repository, new FakeLocales, $translator))->translateNextBatch('dealer');

        $this->assertSame(3, $translator->calls);
        $this->assertSame(3, $result['failed']);
        $this->assertSame(6, $result['remaining']);
    }
}

class FakeLocales implements SupportedLocalesContract
{
    public function codes(): array
    {
        return ['en', 'hi', 'mr'];
    }

    public function translatable(): array
    {
        return ['hi', 'mr'];
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->codes(), true);
    }

    public function default(): string
    {
        return 'en';
    }
}

class FakeTranslator implements TextTranslatorContract
{
    public int $calls = 0;

    public bool $fail = false;

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        $this->calls++;

        if ($this->fail) {
            throw new RuntimeException('translator down');
        }

        return $targetLocale.':'.$text;
    }
}

class FakeAppTranslationRepository implements AppTranslationRepositoryContract
{
    /** @var array<string, array<string, string>> */
    public array $english = [];

    /** @var array<int, array{app: string, key: string, english: string}> */
    public array $rows = [];

    /** @var array<string, string> */
    public array $saved = [];

    public int $saveEnglishCalls = 0;

    public array $lastChangedKeys = [];

    public array $lastSavedItems = [];

    public function englishFor(string $app, string $defaultLocale): array
    {
        return $this->english[$app] ?? [];
    }

    public function saveEnglish(string $app, string $defaultLocale, array $items, array $changedKeys): void
    {
        $this->saveEnglishCalls++;
        $this->lastSavedItems = $items;
        $this->lastChangedKeys = $changedKeys;
    }

    public function translationsFor(string $app, string $locale): array
    {
        return [];
    }

    public function englishRows(?string $app, string $defaultLocale): array
    {
        return array_values(array_filter($this->rows, fn ($row) => $app === null || $row['app'] === $app));
    }

    public function translatedIndex(?string $app, array $locales): array
    {
        return [];
    }

    public function saveTranslation(string $app, string $key, string $locale, string $english, string $value): void
    {
        $this->saved[$app.'|'.$key.'|'.$locale] = $value;
    }
}
