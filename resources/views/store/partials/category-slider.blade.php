@php
    $categoryImages = [
        'fastkart-store/images/grocery/category/1.png',
        'fastkart-store/images/grocery/category/2.png',
        'fastkart-store/images/grocery/category/3.png',
        'fastkart-store/images/grocery/category/4.png',
        'fastkart-store/images/grocery/category/5.png',
        'fastkart-store/images/grocery/category/6.png',
        'fastkart-store/images/grocery/category/7.png',
        'fastkart-store/images/grocery/category/8.png',
    ];
@endphp

@forelse($categories as $category)
    @php
        $categoryUrl = route('store.category', ['category' => $category->slug]);
        $categoryImage = asset($categoryImages[$loop->index % count($categoryImages)]);
    @endphp
    <div>
        <div class="category-box-list">
            <a href="{{ $categoryUrl }}" class="category-name">
                <h4>{{ $category->name }}</h4>
                <h6>{{ (int) ($category->products_count ?? 0) }} items</h6>
            </a>
            <div class="category-box-view">
                <a href="{{ $categoryUrl }}">
                    <img src="{{ $categoryImage }}" class="img-fluid blur-up lazyload" alt="{{ $category->name }}">
                </a>
                <button onclick="location.href='{{ $categoryUrl }}';" class="btn shop-button">
                    <span>Shop Now</span>
                    <i class="fas fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
@empty
    <div>
        <div class="category-box-list">
            <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}" class="category-name">
                <h4>Products</h4>
                <h6>0 items</h6>
            </a>
            <div class="category-box-view">
                <a href="{{ route('store.page', ['page'=>'shop-left-sidebar']) }}">
                    <img src="{{ asset('fastkart-store/images/grocery/category/1.png') }}" class="img-fluid blur-up lazyload" alt="Products">
                </a>
                <button onclick="location.href='{{ route('store.page', ['page'=>'shop-left-sidebar']) }}';" class="btn shop-button">
                    <span>Shop Now</span>
                    <i class="fas fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
@endforelse