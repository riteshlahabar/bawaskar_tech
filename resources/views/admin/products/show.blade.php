@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);

    // Every relation below is eager-loaded by the module's own `with` list, so
    // this page adds no queries of its own.
    $variants = $record->variants->sortBy('sort_order');
    $gallery = $record->images->sortBy('sort_order');
    $videos = $record->media->sortBy('sort_order');
    $translations = $record->translations->sortBy('locale');
    $related = $record->relatedProductLinks;
    $additionalInfo = collect($record->additional_info ?? []);

    $money = fn ($value): string => 'Rs. '.number_format((float) $value, 2);

    // Variant figures are decimal:3 casts, so a whole number arrives as
    // "20.000" — trimmed here rather than in every cell.
    $qty = function ($value): string {
        $number = (float) $value;

        return rtrim(rtrim(number_format($number, 3, '.', ''), '0'), '.') ?: '0';
    };

    $yesNo = fn ($value): string => $value ? 'Yes' : 'No';

    $text = fn ($value): string => filled($value) ? $value : '-';

    $languages = [
        'hi' => 'Hindi', 'mr' => 'Marathi', 'gu' => 'Gujarati',
        'pa' => 'Punjabi', 'te' => 'Telugu', 'kn' => 'Kannada', 'en' => 'English',
    ];
@endphp

<div class="report-page product-show-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($record->storefront_image_url)
                        <img src="{{ $record->storefront_image_url }}" alt="{{ $record->name }}" class="rounded" style="width:72px;height:72px;object-fit:cover;border:1px solid #e6ecea;">
                    @else
                        <span class="report-hero-icon"><i data-feather="package"></i></span>
                    @endif
                    <div>
                        <span class="report-hero-section">{{ $record->category?->name ?? 'Product' }}</span>
                        <h4 class="report-hero-title">{{ $record->name }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $record->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span>
                            @if($record->sku)<span><i data-feather="hash"></i>{{ $record->sku }}</span>@endif
                            <span><i data-feather="layers"></i>{{ $variants->count() }} variant{{ $variants->count() === 1 ? '' : 's' }}</span>
                            <span><i data-feather="truck"></i>{{ $qty($record->available_stock) }} in stock</span>
                            <span><i data-feather="users"></i>{{ $record->is_visible_to_dealers ? 'Dealers' : 'Not for dealers' }} &middot; {{ $record->is_visible_to_customers ? 'Customers' : 'Not for customers' }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-reset" href="{{ route($module['route'].'.index', $query) }}"><i data-feather="arrow-left"></i>Back</a>
                    @if(($module['can_edit'] ?? true) && $can['edit'])
                        <a class="btn btn-theme report-apply" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], $query)) }}"><i data-feather="edit-2"></i>Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card report-table-card">
        <div class="card-body pt-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h5 class="report-section-title mb-2"><i data-feather="info"></i>Basic Information</h5>
                    <ul class="people-info-list">
                        <li><span class="people-info-label">SKU</span><span class="people-info-value">{{ $text($record->sku) }}</span></li>
                        <li><span class="people-info-label">HSN Code</span><span class="people-info-value">{{ $text($record->hsn_code) }}</span></li>
                        <li><span class="people-info-label">Category</span><span class="people-info-value">{{ $text($record->category?->name) }}</span></li>
                        <li><span class="people-info-label">Brand</span><span class="people-info-value">{{ $text($record->brand?->name) }}</span></li>
                        <li><span class="people-info-label">Product Type</span><span class="people-info-value">{{ $text($record->productType?->name) }}</span></li>
                        <li><span class="people-info-label">Unit</span><span class="people-info-value">{{ $text($record->unit?->name) }}</span></li>
                        <li><span class="people-info-label">Homepage Section</span><span class="people-info-value">{{ $text($record->homepageSection?->title) }}</span></li>
                        <li><span class="people-info-label">Sort Order</span><span class="people-info-value">{{ $record->sort_order ?? 0 }}</span></li>
                        <li><span class="people-info-label">Short Description</span><span class="people-info-value">{{ $text($record->short_description) }}</span></li>
                        <li><span class="people-info-label">Created On</span><span class="people-info-value">{{ $record->created_at?->format('d M Y, g:i A') ?? '-' }}</span></li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <h5 class="report-section-title mb-2"><i data-feather="eye"></i>Display &amp; Offer</h5>
                    <ul class="people-info-list">
                        <li><span class="people-info-label">Visible to Dealers</span><span class="people-info-value">{{ $yesNo($record->is_visible_to_dealers) }}</span></li>
                        <li><span class="people-info-label">Visible to Customers</span><span class="people-info-value">{{ $yesNo($record->is_visible_to_customers) }}</span></li>
                        <li><span class="people-info-label">Show on Homepage</span><span class="people-info-value">{{ $yesNo($record->show_on_homepage) }}</span></li>
                        <li><span class="people-info-label">Sale Badge Text</span><span class="people-info-value">{{ $text($record->sale_badge_text) }}</span></li>
                        <li><span class="people-info-label">Sold / Total Quantity</span><span class="people-info-value">{{ $record->sold_quantity ?? 0 }} / {{ $record->total_quantity ?? 0 }}</span></li>
                        <li><span class="people-info-label">Low Stock Text</span><span class="people-info-value">{{ $text($record->low_stock_text) }}</span></li>
                        <li><span class="people-info-label">Offer Timer</span><span class="people-info-value">{{ $record->is_offer_active ? 'On' : 'Off' }}</span></li>
                        <li><span class="people-info-label">Offer Starts</span><span class="people-info-value">{{ $record->offer_start_at?->format('d M Y, g:i A') ?? '-' }}</span></li>
                        <li><span class="people-info-label">Offer Ends</span><span class="people-info-value">{{ $record->offer_end_at?->format('d M Y, g:i A') ?? '-' }}</span></li>
                        <li><span class="people-info-label">Last Updated</span><span class="people-info-value">{{ $record->updated_at?->format('d M Y, g:i A') ?? '-' }}</span></li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="report-section-title"><i data-feather="layers"></i>Size / Pack Variants</h5>
                <span class="report-meta">{{ $variants->count() }} variant{{ $variants->count() === 1 ? '' : 's' }} &middot; {{ $qty($record->available_stock) }} total stock</span>
            </div>

            @if($variants->isEmpty())
                <div class="report-empty">
                    <span class="report-empty-icon"><i data-feather="layers"></i></span>
                    <h5>No variants</h5>
                    <p>This product has no size or pack variant yet. Add one from the Edit form.</p>
                </div>
            @else
                <div class="table-responsive report-table-wrap">
                    <table class="table report-table mb-0">
                        <thead>
                            <tr>
                                <th>Pack</th>
                                <th>Variant SKU</th>
                                <th>HSN</th>
                                <th class="text-end">GST %</th>
                                <th class="text-end">Units / Case</th>
                                <th class="text-end">MRP</th>
                                <th class="text-end">Dealer Price</th>
                                <th class="text-end">Dealer Case Price</th>
                                <th class="text-end">Customer Price</th>
                                <th class="text-end">Available Stock</th>
                                <th>Main</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variants as $variant)
                                @php
                                    $perCase = max(1, (float) $variant->units_per_case);
                                    $dealerPrice = (float) ($variant->dealer_price ?? $record->dealer_price ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $variant->display_name }}</td>
                                    <td>{{ $text($variant->variant_sku) }}</td>
                                    <td>{{ $text($variant->hsn_code ?? $record->hsn_code) }}</td>
                                    <td class="text-end">{{ $qty($variant->gst_percent ?? $record->gst_percent) }}</td>
                                    <td class="text-end">{{ $qty($perCase) }}</td>
                                    <td class="text-end">{{ $money($variant->mrp ?? $record->mrp) }}</td>
                                    <td class="text-end">{{ $money($dealerPrice) }}</td>
                                    <td class="text-end">{{ $money($dealerPrice * $perCase) }}</td>
                                    <td class="text-end">{{ $money($variant->customer_price ?? $record->customer_price) }}</td>
                                    <td class="text-end">{{ $qty($variant->available_stock) }}</td>
                                    <td>{{ $variant->is_default ? 'Yes' : '-' }}</td>
                                    <td><span class="badge {{ $variant->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $variant->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <hr class="my-4">

            <h5 class="report-section-title mb-2"><i data-feather="image"></i>Images &amp; Gallery</h5>
            @if($gallery->isEmpty() && ! $record->storefront_image_url)
                <div class="report-empty">
                    <span class="report-empty-icon"><i data-feather="image"></i></span>
                    <h5>No images</h5>
                    <p>No product image or gallery image has been uploaded.</p>
                </div>
            @else
                <div class="d-flex flex-wrap gap-3">
                    @foreach($gallery as $image)
                        @if($image->url)
                            <a href="{{ $image->url }}" target="_blank" rel="noopener" title="{{ $image->is_primary ? 'Main image' : 'Gallery image' }}">
                                <img src="{{ $image->url }}" alt="{{ $record->name }}" class="rounded" style="width:96px;height:96px;object-fit:cover;border:1px solid {{ $image->is_primary ? '#0DA487' : '#e6ecea' }};">
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($videos->isNotEmpty())
                <hr class="my-4">
                <h5 class="report-section-title mb-2"><i data-feather="video"></i>Product Videos</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table report-table mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Source</th>
                                <th>Language</th>
                                <th>Link</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($videos as $video)
                                <tr>
                                    <td>{{ $text($video->title) }}</td>
                                    <td>{{ str((string) $video->source_type)->replace('_', ' ')->title() }}</td>
                                    <td>{{ $text($video->language) }}</td>
                                    <td>
                                        @if($video->url)
                                            <a href="{{ $video->url }}" target="_blank" rel="noopener">Open</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $video->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $video->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <hr class="my-4">

            <h5 class="report-section-title mb-3"><i data-feather="file-text"></i>Bottom Details</h5>
            <div class="row g-4">
                @foreach(['Description' => $record->description, 'Benefits' => $record->benefits, 'Usage Instructions' => $record->usage_instructions, 'Crop Information' => $record->crop_information, 'Care Instructions' => $record->care_instructions] as $label => $body)
                    @if(filled($body))
                        <div class="col-lg-6">
                            <span class="people-info-label d-block mb-1">{{ $label }}</span>
                            <div class="people-info-value" style="white-space:pre-line;">{{ $body }}</div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if($additionalInfo->isNotEmpty())
                <h5 class="report-section-title mt-4 mb-2"><i data-feather="list"></i>Additional Information</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table report-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40%">Label</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($additionalInfo as $row)
                                <tr>
                                    <td>{{ $text(data_get($row, 'label')) }}</td>
                                    <td>{{ $text(data_get($row, 'value')) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($translations->isNotEmpty())
                <hr class="my-4">
                <h5 class="report-section-title mb-2"><i data-feather="globe"></i>Language Translations</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table report-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:140px">Language</th>
                                <th style="width:30%">Product Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($translations as $translation)
                                <tr>
                                    <td>{{ $languages[$translation->locale] ?? strtoupper((string) $translation->locale) }}</td>
                                    <td>{{ $text($translation->name) }}</td>
                                    <td>{{ $text($translation->description) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($related->isNotEmpty())
                <hr class="my-4">
                <h5 class="report-section-title mb-2"><i data-feather="link"></i>Related Products</h5>
                <ul class="people-info-list">
                    @foreach($related as $link)
                        <li>
                            <span class="people-info-label">{{ $link->relatedProduct?->sku ?? '-' }}</span>
                            <span class="people-info-value">{{ $link->relatedProduct?->name ?? 'Removed product' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(filled($record->meta_title) || filled($record->meta_description) || filled($record->meta_keywords))
                <hr class="my-4">
                <h5 class="report-section-title mb-2"><i data-feather="search"></i>SEO</h5>
                <ul class="people-info-list">
                    <li><span class="people-info-label">Meta Title</span><span class="people-info-value">{{ $text($record->meta_title) }}</span></li>
                    <li><span class="people-info-label">Meta Description</span><span class="people-info-value">{{ $text($record->meta_description) }}</span></li>
                    <li><span class="people-info-label">Meta Keywords</span><span class="people-info-value">{{ $text($record->meta_keywords) }}</span></li>
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
