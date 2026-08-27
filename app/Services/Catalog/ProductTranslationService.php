<?php

namespace App\Services\Catalog;

use App\Contracts\Catalog\ProductTranslationRepositoryContract;
use App\Contracts\Catalog\ProductTranslationServiceContract;
use App\Contracts\Catalog\TextTranslatorContract;
use App\Models\Catalog\Product;

/**
 * SRP:
 * Coordinates Product translation business rules only.
 *
 * OCP:
 * Translation provider and persistence implementation are replaceable.
 *
 * DIP:
 * Depends only on focused contracts.
 */
class ProductTranslationService
    implements ProductTranslationServiceContract
{
    private const LOCALES = [
        'hi',
        'mr',
        'gu',
        'kn',
        'te',
    ];

    public function __construct(
        private readonly TextTranslatorContract $translator,
        private readonly ProductTranslationRepositoryContract $repository
    ) {
    }

    public function translatePayload(
        string $name,
        ?string $description
    ): array {
        $translations = [];

        foreach (self::LOCALES as $locale) {
            $translations[$locale] = [
                'name' => $this->translator->translate(
                    $name,
                    'en',
                    $locale
                ),

                'description' => filled($description)
                    ? $this->translator->translate(
                        (string) $description,
                        'en',
                        $locale
                    )
                    : '',
            ];
        }

        return $translations;
    }

    public function extract(array &$data): array
    {
        $translations = [];

        foreach (self::LOCALES as $locale) {
            $nameKey =
                'translation_'.$locale.'_name';

            $descriptionKey =
                'translation_'.$locale.'_description';

            $translations[$locale] = [
                'name' => trim(
                    (string) ($data[$nameKey] ?? '')
                ),

                'description' => trim(
                    (string) ($data[$descriptionKey] ?? '')
                ),
            ];

            unset(
                $data[$nameKey],
                $data[$descriptionKey]
            );
        }

        return $translations;
    }

    public function sync(
        Product $product,
        array $translations
    ): void {
        foreach ($translations as $locale => $translation) {
            $name =
                trim(
                    (string) ($translation['name'] ?? '')
                );

            $description =
                trim(
                    (string) ($translation['description'] ?? '')
                );

            if ($name === '' && $description === '') {
                $this->repository->deleteLocale(
                    (int) $product->getKey(),
                    $locale
                );

                continue;
            }

            $this->repository->upsert(
                (int) $product->getKey(),
                $locale,
                $name !== ''
                    ? $name
                    : (string) $product->name,
                $description !== ''
                    ? $description
                    : null
            );
        }
    }

    public function formData(Product $product): array
    {
        $translations =
            $this->repository->getByProductId(
                (int) $product->getKey()
            );

        $data = [];

        foreach (self::LOCALES as $locale) {
            $translation =
                $translations[$locale] ?? [];

            $data[
                'translation_'.$locale.'_name'
            ] = $translation['name'] ?? null;

            $data[
                'translation_'.$locale.'_description'
            ] = $translation['description'] ?? null;
        }

        return $data;
    }
}