<?php

namespace App\Http\Controllers\Admin\StorefrontRows;

use App\Http\Controllers\Controller;
use App\Models\Storefront\StorefrontSection;
use Illuminate\Contracts\View\View;

class StorefrontRowController extends Controller
{
    public function show(string $row): View
    {
        $rows = config('storefront_rows', []);
        abort_unless(isset($rows[$row]), 404);

        $rowConfig = $rows[$row];

        if (! empty($rowConfig['section_key'])) {
            StorefrontSection::query()->firstOrCreate(
                ['section_key' => $rowConfig['section_key']],
                [
                    'title' => $rowConfig['frontend_title'] ?? $rowConfig['title'],
                    'section_type' => $rowConfig['section_type'] ?? 'product',
                    'source_type' => 'manual',
                    'product_limit' => 12,
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }

        return view('admin.storefront-rows.show', [
            'rowKey' => $row,
            'rowConfig' => $rowConfig,
            'pageTitle' => $rowConfig['title'],
            'breadcrumbs' => ['Admin', 'Storefront', $rowConfig['title']],
        ]);
    }
}