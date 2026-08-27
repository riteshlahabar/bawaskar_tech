@php
    $variantRows = old('variants', $formData['variants'] ?? []);
    $variantRows = is_array($variantRows) ? $variantRows : [];
    $variantWarehouses = $options['variant_warehouses'] ?? [];
@endphp
<div data-product-variants-repeater>
    <div data-repeater-rows>
        @foreach($variantRows as $variantIndex => $variantRow)
            @include('admin.products.partials.variants.row', ['row' => $variantRow, 'index' => $variantIndex, 'variantWarehouses' => $variantWarehouses])
        @endforeach
    </div>
    <template data-repeater-template>
        @include('admin.products.partials.variants.row', ['row' => [], 'index' => '__INDEX__', 'variantWarehouses' => $variantWarehouses])
    </template>
    <button type="button" class="btn btn-outline-primary" data-add-repeater-row><i class="iconoir-plus me-1"></i>Add Another Size / Pack</button>
    <small class="text-muted d-block mt-2">Main Display Pack is used by direct Add to Cart. Dealer: quantity 1 = one case. Customer: quantity 1 = one retail pack.</small>
</div>
