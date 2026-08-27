@php
    $row = (array) ($row ?? []);
    $index = $index ?? '__INDEX__';
    $isDefault = filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOL);
    $isActive = ! array_key_exists('is_active', $row) || filter_var($row['is_active'], FILTER_VALIDATE_BOOL);
@endphp
<div class="border rounded p-3 mb-3 bg-light" data-product-variant-row>
    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Size / Pack Variant</strong>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-repeater-row><i class="iconoir-trash"></i> Remove</button>
    </div>
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Size <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0.001" step="0.001" name="variants[{{ $index }}][size_value]" value="{{ $row['size_value'] ?? '' }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Unit <span class="text-danger">*</span></label>
            <select class="form-select" name="variants[{{ $index }}][size_unit]" required>
                <option value="">Select Unit</option>
                @foreach(['ML'=>'ML','LTR'=>'LTR','GM'=>'GM','KG'=>'KG','PCS'=>'PCS'] as $key => $label)
                    <option value="{{ $key }}" @selected(($row['size_unit'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Variant SKU</label>
            <input class="form-control" type="text" maxlength="100" name="variants[{{ $index }}][variant_sku]" value="{{ $row['variant_sku'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Units in One Case <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="1" step="1" name="variants[{{ $index }}][units_per_case]" value="{{ $row['units_per_case'] ?? 1 }}" data-units-per-case required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" min="0" step="1" name="variants[{{ $index }}][sort_order]" value="{{ $row['sort_order'] ?? 0 }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div>
                <input type="hidden" name="variants[{{ $index }}][is_active]" value="0">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="variants[{{ $index }}][is_active]" value="1" id="variant-active-{{ $index }}" @checked($isActive)>
                    <label class="form-check-label" for="variant-active-{{ $index }}">Active</label>
                </div>
                <input type="hidden" name="variants[{{ $index }}][is_default]" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="variants[{{ $index }}][is_default]" value="1" id="variant-default-{{ $index }}" data-main-display-pack @checked($isDefault)>
                    <label class="form-check-label" for="variant-default-{{ $index }}">Main Display Pack</label>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">MRP per Retail Pack <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][mrp]" value="{{ $row['mrp'] ?? '' }}" data-variant-mrp required>
            <small class="text-muted">MRP per case: Rs. <span data-case-mrp>0.00</span></small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Dealer Rate per Retail Pack (GST Inclusive) <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][dealer_price]" value="{{ $row['dealer_price'] ?? '' }}" data-variant-dealer-price required>
            <small class="text-muted">Dealer rate per case: Rs. <span data-case-dealer>0.00</span></small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Customer Price per Retail Pack <span class="text-danger">*</span></label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][customer_price]" value="{{ $row['customer_price'] ?? '' }}" required>
            <small class="text-muted">Customer quantity 1 = one retail pack.</small>
        </div>

        <div class="col-12"><hr class="my-0"><small class="text-muted">Optional opening stock for this size. Stock quantity is always entered in retail packs/bottles.</small></div>
        <div class="col-md-3">
            <label class="form-label">Warehouse</label>
            <select class="form-select" name="variants[{{ $index }}][warehouse_id]">
                <option value="">Select Warehouse</option>
                @foreach($variantWarehouses ?? [] as $key => $label)
                    <option value="{{ $key }}" @selected((string) ($row['warehouse_id'] ?? '') === (string) $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Batch No</label>
            <input class="form-control" type="text" maxlength="80" name="variants[{{ $index }}][batch_no]" value="{{ $row['batch_no'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Manufacturing Date</label>
            <input class="form-control" type="date" name="variants[{{ $index }}][manufacturing_date]" value="{{ $row['manufacturing_date'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Expiry Date</label>
            <input class="form-control" type="date" name="variants[{{ $index }}][expiry_date]" value="{{ $row['expiry_date'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Purchase Price per Retail Pack</label>
            <input class="form-control" type="number" min="0" step="0.01" name="variants[{{ $index }}][purchase_price]" value="{{ $row['purchase_price'] ?? 0 }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Opening Stock (Retail Packs)</label>
            <input class="form-control" type="number" min="0" step="0.001" name="variants[{{ $index }}][opening_stock_quantity]" value="{{ $row['opening_stock_quantity'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Low Stock Alert (Retail Packs)</label>
            <input class="form-control" type="number" min="0" step="0.001" name="variants[{{ $index }}][low_stock_alert]" value="{{ $row['low_stock_alert'] ?? 0 }}">
        </div>
    </div>
</div>
