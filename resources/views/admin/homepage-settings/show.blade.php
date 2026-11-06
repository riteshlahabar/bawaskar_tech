@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row g-3">
    {{-- Details first, as a table, with Section Content as a second table
         below it - the two read as one page instead of two columns of
         different shapes. --}}
    <div class="col-12">
        <div class="card card-table">
            <div class="card-body">
                <div class="title-header option-title">
                    {{-- `fs-6` on purpose: the template's .title-header h5 is 22px
                         bold, which dwarfs a 14px details table. --}}
                    <h5 class="mb-0 fs-6">{{ $record->title }}</h5>
                    <div class="right-options">
                        <ul>
                            <li><a class="btn btn-solid" href="{{ route('admin.homepage-settings.edit', $record->id) }}">Edit Section</a></li>
                            <li><a class="btn btn-outline-secondary" href="{{ route('admin.homepage-settings.index') }}">Back</a></li>
                        </ul>
                    </div>
                </div>

                <div class="table-responsive">
                    {{-- `table-borderless` kills Bootstrap's per-cell bottom border,
                         which was drawing an underline under every value. The
                         zebra rows from `theme-table` separate them instead. --}}
                    <table class="table theme-table table-borderless align-middle mb-0 admin-detail-table">
                        <tbody>
                            <tr>
                                <th style="width:220px;">Section Type</th>
                                <td>{{ str($record->section_type)->replace('_', ' ')->title() }}</td>
                                <th style="width:220px;">Layout Type</th>
                                <td>{{ $record->layout_type ? str($record->layout_type)->replace('_', ' ')->title() : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>{{ $record->category?->name ?: '-' }}</td>
                                <th>Product Limit</th>
                                <td>{{ $record->product_limit }}</td>
                            </tr>
                            <tr>
                                <th>Display Row Order</th>
                                <td>{{ $record->sort_order }}</td>
                                <th>Status</th>
                                <td>
                                    <span class="badge {{ $record->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $record->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @if($record->usesContentRowProducts())
                                <tr>
                                    <th>Offer Timer</th>
                                    <td colspan="3">
                                        <span class="badge {{ $record->dealTimerRunning() ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $record->dealTimerRunning() ? 'Running' : 'Off' }}
                                        </span>
                                        @if($record->deal_starts_at)
                                            <span class="ms-2 text-muted small">from {{ $record->deal_starts_at->format('d M Y, g:i A') }}</span>
                                        @endif
                                        @if($record->deal_ends_at)
                                            <span class="ms-2 text-muted small">until {{ $record->deal_ends_at->format('d M Y, g:i A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-table">
            <div class="card-body">
                <div class="title-header option-title">
                    <div>
                        <h5 class="mb-1 fs-6">Section Content</h5>
                        @if($record->usesContentRowProducts())
                            <small class="text-muted">The products on this row are picked here &mdash; row 1 is the Special Offer card, the rest are the stack beside it.</small>
                        @elseif($record->isProductSection() || $record->section_type === 'category_section')
                            <small class="text-muted">Products are assigned from Product Add/Edit using Homepage Section Title. This page only defines homepage row/design.</small>
                        @else
                            <small class="text-muted">Banner, offer, coupon, service, blog and video content is added here &mdash; not from the Products form.</small>
                        @endif
                    </div>
                    @if($record->hasContentEditor())
                        <div class="right-options">
                            <ul>
                                <li><a class="btn btn-solid" href="{{ route('admin.homepage-settings.content', $record->id) }}">Add / Edit Content</a></li>
                            </ul>
                        </div>
                    @endif
                </div>

                @if(in_array($record->section_type, ['product_section', 'category_section', 'top_selling_section'], true))
                    <div class="alert alert-info">
                        @if($record->section_type === 'product_section')
                            <strong>No duplicate product adding required.</strong>
                            This row shows active products from selected category/source.
                        @elseif($record->section_type === 'top_selling_section')
                            <strong>Pick the products below, in display order.</strong>
                            The first row is the Special Offer card, and the countdown over it is set in that same first row of the content editor.
                        @else
                            <strong>No duplicate product adding required.</strong>
                            This row uses active categories.
                        @endif
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table theme-table table-borderless align-middle mb-0 admin-detail-table">
                        <thead>
                            <tr>
                                <th>Image/Icon</th>
                                <th>Title</th>
                                <th>Linked Product</th>
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
                                    <td>{{ $item->product?->name ?: '-' }}</td>
                                    <td>{{ $item->coupon_code ?: '-' }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ $record->hasContentEditor() ? route('admin.homepage-settings.content', $record->id) : route('admin.homepage-setting-items.edit', $item->id) }}">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No content added yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- The "Products On This Row" card is gone: on every row but a Product
         Section it could only ever say "no products", and the count is not
         what this page is for. What CANNOT go is the transfer form - a row
         refuses to delete while products sit on it and the error points here
         for the fix - so it is kept, and only when there is something to fix.
         (HomepageSectionProductsContract, item 40.) --}}
    @if($record->products->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <strong>{{ $record->products->count() }} product(s) are placed on this row, so it cannot be deleted yet.</strong>
                        Move them to another homepage product row below, or delete them from Products, and then delete this row.
                    </div>

                    <form method="POST" action="{{ route('admin.homepage-settings.transfer-products', $record->id) }}" class="row g-2 align-items-end">
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
                </div>
            </div>
        </div>
    @endif
</div>
@endsection