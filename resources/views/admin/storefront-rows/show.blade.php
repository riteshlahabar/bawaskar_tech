@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <h4 class="mb-1">{{ $rowConfig['title'] }}</h4>
            <p class="text-muted mb-0">
                Frontend section: <strong>{{ $rowConfig['frontend_title'] ?? $rowConfig['title'] }}</strong>
            </p>
        </div>

        <div class="row g-3">
            @if(!empty($rowConfig['placement']))
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h5 class="mb-2">Banners</h5>
                            <p class="text-muted small mb-3">
                                Manage {{ $rowConfig['count'] ?? 'banner images' }} for this row only.
                            </p>
                            <a class="btn btn-primary w-100"
                               href="{{ route('admin.storefront-banners.index', ['placement' => $rowConfig['placement'], 'row_title' => $rowConfig['title'].' - Banners']) }}">
                                Manage Banners
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($rowConfig['section_key']))
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h5 class="mb-2">Heading</h5>
                            <p class="text-muted small mb-3">
                                Edit heading like Fruits & Vegetables, Drinks, Personal Care.
                            </p>
                            <a class="btn btn-outline-primary w-100"
                               href="{{ route('admin.storefront-sections.index', ['section_key' => $rowConfig['section_key'], 'row_title' => $rowConfig['title'].' - Heading']) }}">
                                Manage Heading
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if(($rowConfig['type'] ?? '') === 'products')
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h5 class="mb-2">Products</h5>
                            <p class="text-muted small mb-3">
                                Select products from Products master for this row.
                            </p>
                            <a class="btn btn-success w-100"
                               href="{{ route('admin.storefront-section-products.index', ['section_key' => $rowConfig['section_key'], 'row_title' => $rowConfig['title'].' - Products']) }}">
                                Manage Products
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if(($rowConfig['type'] ?? '') === 'categories')
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h5 class="mb-2">Categories</h5>
                            <p class="text-muted small mb-3">
                                Categories are managed from Category master.
                            </p>
                            <a class="btn btn-success w-100" href="{{ route('admin.categories.index') }}">
                                Manage Categories
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if(($rowConfig['type'] ?? '') === 'blog')
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h5 class="mb-2">Blog</h5>
                            <p class="text-muted small mb-3">
                                Blog module will be linked here later.
                            </p>
                            <button class="btn btn-secondary w-100" disabled>Blog Module Pending</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="alert alert-info mt-4 mb-0">
            Products are added from <strong>Products</strong> menu. This row only controls what appears on homepage.
        </div>
    </div>
</div>
@endsection