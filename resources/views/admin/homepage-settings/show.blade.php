@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">{{ $record->title }}</h5>
                <p class="mb-1"><strong>Type:</strong> {{ str($record->section_type)->replace('_', ' ')->title() }}</p>
                <p class="mb-1"><strong>Layout:</strong> {{ $record->layout_type ?: '-' }}</p>
<p class="mb-1"><strong>Category:</strong> {{ $record->category?->name ?: '-' }}</p>
                <p class="mb-1"><strong>Product Limit:</strong> {{ $record->product_limit }}</p>
                <p class="mb-1"><strong>Item Limit:</strong> {{ $record->item_limit ?: '-' }}</p>
                <p class="mb-1"><strong>Image Note:</strong> {{ $record->image_size_note ?: '-' }}</p>
                <p class="mb-1"><strong>Sort Order:</strong> {{ $record->sort_order }}</p>
                <p class="mb-0"><strong>Status:</strong> {{ $record->is_active ? 'Active' : 'Inactive' }}</p>

                <p class="mb-0 mt-2"><strong>Products On This Row:</strong> {{ $record->products->count() }}</p>

                <div class="d-flex gap-2 mt-4">
                    <a class="btn btn-primary" href="{{ route('admin.homepage-settings.edit', $record->id) }}">Edit Section</a>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.homepage-settings.index') }}">Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Section Content</h5>
                        <small class="text-muted">Products are assigned from Product Add/Edit using Homepage Section Title. This page only defines homepage row/design.</small>
                    </div>
                    <a class="btn btn-success" href="{{ route('admin.homepage-setting-items.create', ['section_id' => $record->id, 'row_title' => $record->title.' Content']) }}">
                        Add Content
                    </a>
                </div>

                @if(in_array($record->section_type, ['product_section', 'category_section', 'top_selling_section'], true))
                    <div class="alert alert-info">
                        <strong>No duplicate product adding required.</strong>
                        @if($record->section_type === 'product_section')
                            This row shows active products from selected category/source.
                        @elseif($record->section_type === 'top_selling_section')
                            This row uses products where <strong>Top Selling Product</strong> is enabled. Timer card uses product where <strong>Deal Timer Product</strong> is enabled.
                        @else
                            This row uses active categories.
                        @endif
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Image/Icon</th>
                                <th>Title</th>
                                <th>Coupon</th>
                                <th>Sort</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($record->items as $item)
                                <tr>
                                    <td>
                                        @php($preview = $item->image_path ?: $item->logo_image_path)
                                        @if($preview)
                                            <img src="{{ asset($preview) }}" style="width:70px;height:45px;object-fit:cover;border-radius:6px;border:1px solid #ddd;" alt="">
                                        @else
                                            {{ $item->icon_key ?: '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $item->title ?: $item->highlight_text ?: '-' }}</td>
                                    <td>{{ $item->coupon_code ?: '-' }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.homepage-setting-items.edit', $item->id) }}">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No content added yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- This row cannot be deleted while products are placed on it, so the
         products and the one-click transfer live on the same page as the
         delete, instead of leaving the admin to edit each product by hand. --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-1">Products On This Row</h5>
                <small class="text-muted">Assigned from Product Add/Edit using Homepage Section Title.</small>

                @if($record->products->isEmpty())
                    <div class="alert alert-success mt-3 mb-0">
                        No products are placed on this row, so it can be deleted.
                    </div>
                @else
                    <div class="alert alert-warning mt-3">
                        <strong>{{ $record->products->count() }} product(s) are placed on this row, so it cannot be deleted yet.</strong>
                        Move them to another homepage section below, or delete them from Products, and then delete this row.
                    </div>

                    <form method="POST" action="{{ route('admin.homepage-settings.transfer-products', $record->id) }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label" for="target_section_id">Transfer all {{ $record->products->count() }} product(s) to</label>
                            <select class="form-select @error('target_section_id')is-invalid @enderror" name="target_section_id" id="target_section_id" required>
                                <option value="">Select homepage section</option>
                                @foreach($transferTargets ?? [] as $target)
                                    <option value="{{ $target->id }}" @selected(old('target_section_id') == $target->id)>
                                        {{ $target->title }} ({{ str($target->section_type)->replace('_', ' ')->title() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('target_section_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">Transfer Products</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->products as $product)
                                    <tr>
                                        <td>{{ $product->sku ?: '-' }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category?->name ?: '-' }}</td>
                                        <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product->id) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection