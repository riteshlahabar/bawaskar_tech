@foreach($homeProductSections as $homeProductSection)
    @php
        $section = $homeProductSection['section'];
        $sectionProducts = collect($homeProductSection['products']);
    @endphp
    <section class="product-section-3">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>{{ $section->title }}</h2>
                @if($section->subtitle)
                    <span class="title-leaf"><span>{{ $section->subtitle }}</span></span>
                @endif
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="slider-7_1 arrow-slider img-slider">
                        @foreach($sectionProducts as $product)
                            @include('store.partials.product-card-4', ['product' => $product])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endforeach