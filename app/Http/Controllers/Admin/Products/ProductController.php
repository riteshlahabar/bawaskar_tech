<?php

namespace App\Http\Controllers\Admin\Products;

use App\Contracts\Catalog\BulkProductCategoryContract;
use App\Contracts\Catalog\Product\ProductFormContract;
use App\Contracts\Catalog\Product\ProductInputContract;
use App\Contracts\Catalog\Product\ProductValidationContract;
use App\Contracts\Catalog\Product\ProductWorkflowContract;
use App\Data\Catalog\ProductSaveData;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * SRP: Product CRUD orchestration only.
 * DIP: Depends only on focused Product contracts.
 */
final class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    public function __construct(
        AdminModuleServices $modules,
        private readonly ProductValidationContract $validation,
        private readonly ProductInputContract $input,
        private readonly ProductWorkflowContract $workflow,
        private readonly ProductFormContract $form,
        private readonly BulkProductCategoryContract $bulkCategories,
    ) {
        parent::__construct($modules);
    }

    /**
     * Moves the products ticked on the listing into one category in a single
     * update, instead of reopening the full form (which forces at least one
     * variant) for every product.
     */
    public function bulkCategory(Request $request): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);

        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', Rule::exists('products', 'id')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
        ], [
            'product_ids.required' => 'Select at least one product first.',
            'category_id.required' => 'Choose the category to move these products into.',
            'category_id.exists' => 'That category no longer exists.',
        ]);

        $category = Category::query()->findOrFail($data['category_id']);
        $selected = count($data['product_ids']);
        $moved = $this->bulkCategories->move($data['product_ids'], $category);

        $this->bumpCacheVersionForModule();

        // Both numbers are reported: a bare "0 moved" cannot tell apart ids
        // that never arrived from products that were already in the target.
        return back()->with('success', $moved === 0
            ? 'No change: all '.$selected.' selected product(s) were already in "'.$category->name.'".'
            : 'Moved '.$moved.' of '.$selected.' selected product(s) into "'.$category->name.'".');
    }

    protected function rules(array $module, ?Model $record = null, ?string $channel = null): array
    {
        return $this->validation->extend(
            parent::rules($module, $record, $channel),
            $record instanceof Product ? $record : null,
        );
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $prepared = parent::prepareData($validated, $request, $module);

        return [
            '__product_save_data' => $this->input->make(
                $prepared,
                $request->all(),
                $request->allFiles(),
                $module,
            ),
        ];
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $payload = $data['__product_save_data'] ?? null;
        abort_unless($payload instanceof ProductSaveData, 500, 'Invalid product save payload.');

        $product = $this->workflow->save(
            $payload,
            $record instanceof Product ? $record : null,
        );

        $this->bumpCacheVersionForModule();

        return $product;
    }

    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);

        return $record instanceof Product
            ? $this->form->augmentData($record, $data)
            : $data;
    }

    protected function formOptions(array $module, ?string $channel = null): array
    {
        return $this->form->augmentOptions(parent::formOptions($module, $channel));
    }
}
