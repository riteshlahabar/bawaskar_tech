<?php

namespace App\Http\Controllers\Admin\ProductHomepageSettings;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Http\Request;

class ProductHomepageSettingController extends AdminModuleController
{
    protected string $moduleKey = 'homepage-settings';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        $sectionType = (string) ($data['section_type'] ?? $request->input('section_type'));

        $data['source_type'] = $this->sourceTypeForSection($sectionType);

        if ($sectionType !== 'product_section') {
            $data['category_id'] = null;
        }

        if (empty($data['product_limit'])) {
            $data['product_limit'] = 8;
        }

        if (empty($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        return $data;
    }

    private function sourceTypeForSection(string $sectionType): string
    {
        return match ($sectionType) {
            'hero_slider',
            'top_small_banners',
            'offer_section' => 'banners',

            'category_section' => 'categories',

            'product_section' => 'category_products',

            'coupon_section' => 'coupon_items',

            'top_selling_section' => 'top_selling_products',

            'strip_offer_banner' => 'text',

            'service_section' => 'services',

            default => 'none',
        };
    }
}