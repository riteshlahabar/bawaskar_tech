<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Contracts\Catalog\Api\TranslationCatalogContract;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Catalog\TranslationCatalogRequest;
use Illuminate\Http\JsonResponse;

final class TranslationCatalogController extends ApiController
{
    public function __construct(
        private readonly TranslationCatalogContract $catalog
    ) {
    }

    public function index(TranslationCatalogRequest $request): JsonResponse
    {
        $locale = (string) $request->validated('locale');

        return $this->success([
            'locale' => $locale,
            'translations' => $this->catalog->translations($locale),
        ]);
    }
}
