<?php

namespace App\Http\Controllers\Admin\ProductHomepageSettings;

use App\Contracts\Catalog\HomepageSectionProductsContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\ProductHomepageSection;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductHomepageSettingController extends AdminModuleController
{
    protected string $moduleKey = 'homepage-settings';

    public function __construct(AdminModuleServices $modules, private readonly HomepageSectionProductsContract $sectionProducts)
    {
        parent::__construct($modules);
    }

    /**
     * A row that still has products on it is not deleted. The database would
     * only unlink them (`nullOnDelete`), leaving products with no homepage
     * placement and no trace of where they were, so admin is asked to transfer
     * or delete those products first.
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_delete'] ?? true), 403);

        $section = $this->findRecord($id);
        $attached = $this->sectionProducts->attachedCount($section);

        if ($attached > 0) {
            return back()->with('error', $attached.' product(s) are still placed on "'.$section->title.'". Transfer them to another homepage section, or delete them, before deleting this row.');
        }

        return parent::destroy($id);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $blocked = $this->sectionProducts->blockedTitles((array) $request->input('selected_ids', []));

        if ($blocked !== []) {
            return back()->with('error', 'These rows still have products placed on them: '.implode(', ', $blocked).'. Transfer or delete those products first.');
        }

        return parent::bulkDestroy($request);
    }

    public function show(int|string $id): View
    {
        return parent::show($id)->with([
            'transferTargets' => $this->sectionProducts->transferTargets($this->findRecord($id)),
        ]);
    }

    /** Moves every product on this row onto another row in one step. */
    public function transferProducts(Request $request, int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);

        $section = $this->findRecord($id);

        $data = $request->validate([
            'target_section_id' => ['required', 'integer', Rule::exists('product_homepage_sections', 'id'), Rule::notIn([$section->getKey()])],
        ], [
            'target_section_id.required' => 'Choose the homepage section to move these products into.',
            'target_section_id.not_in' => 'Choose a different homepage section.',
            'target_section_id.exists' => 'That homepage section no longer exists.',
        ]);

        $target = ProductHomepageSection::query()->findOrFail($data['target_section_id']);
        $moved = $this->sectionProducts->transfer($section, $target);
        $this->bumpCacheVersionForModule();

        return back()->with('success', $moved.' product(s) moved to "'.$target->title.'".');
    }

    protected function rules(array $module, $record = null, ?string $channel = null): array
    {
        $rules = parent::rules($module, $record, $channel);

        $uniqueSortOrder = Rule::unique('product_homepage_sections', 'sort_order');

        if ($record) {
            $uniqueSortOrder->ignore($record->getKey());
        }

        $rules['sort_order'] = ['required', 'integer', 'min:0', $uniqueSortOrder];

        return $rules;
    }

    protected function validationMessages(array $module): array
    {
        return array_merge(parent::validationMessages($module), [
            'sort_order.required' => 'Enter row order.',
            'sort_order.integer' => 'Row order must be a number.',
            'sort_order.min' => 'Row order must be 0 or greater.',
            'sort_order.unique' => 'This row order already exists. Change the previous row order or enter a new row order.',
        ]);
    }

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

        if (! isset($data['sort_order']) || $data['sort_order'] === '') {
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

            'blog_section' => 'blog_items',

            default => 'none',
        };
    }
}
