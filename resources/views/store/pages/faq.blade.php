<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Store">
    <meta name="keywords" content="Bawaskar Farmer Store">
    <meta name="author" content="Bawaskar Farmer Store">
    <link rel="icon" href="{{ asset('fastkart-store/images/favicon/1.png') }}" type="image/x-icon">
    <title>Bawaskar Farmer Store</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">

    <!-- bootstrap css -->
    <link id="rtl-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/vendors/bootstrap.css') }}">

    <!-- Iconly css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/bulk-style.css') }}">

    <!-- Template css -->
    <link id="color-link" rel="stylesheet" type="text/css" href="{{ asset('fastkart-store/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-store/css/bawaskar-store.css') }}?v={{ filemtime(public_path('fastkart-store/css/bawaskar-store.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <!-- Loader Start -->
    <div class="fullpage-loader">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <!-- Loader End -->

    <!-- Header Start -->
    @include('store.partials.header')
    <!-- Header End -->

    <!-- mobile fix menu start -->
    <div class="mobile-menu d-md-none d-block mobile-cart">
        <ul>
            <li class="active">
                <a href="{{ route('store.home') }}">
                    <i class="iconly-Home icli"></i>
                    <span>{{ web_t('nav.home', 'Home') }}</span>
                </a>
            </li>

            <li class="mobile-category">
                <a href="javascript:void(0)">
                    <i class="iconly-Category icli js-link"></i>
                    <span>Category</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'search']) }}" class="search-box">
                    <i class="iconly-Search icli"></i>
                    <span>{{ web_t('header.search', 'Search') }}</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'wishlist']) }}" class="notifi-wishlist">
                    <i class="iconly-Heart icli"></i>
                    <span>My Wish</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.page', ['page'=>'cart']) }}">
                    <i class="iconly-Bag-2 icli fly-cate"></i>
                    <span>Cart</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- mobile fix menu end -->

    <!-- Breadcrumb Section Start -->
    <section class="faq-breadcrumb pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadcrumb-contain">
                        <h2>Help Center</h2>
                        <p>We are glad having you here looking for the answer. As our team hardly working on the
                            product, feel free to ask any questions. We Believe only your feedback might move us
                            forward.</p>
                        <div class="faq-form-tag">
                            <div class="input-group">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" class="form-control" id="exampleFormControlInput1"
                                    placeholder="name@example.com">
                                <div class="dropdown">
                                    <button class="btn btn-md faq-dropdown-button dropdown-toggle" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown">All
                                        Product <i class="fa-solid fa-angle-down ms-2"></i></button>
                                    <ul class="dropdown-menu faq-dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#">Action</a></li>
                                        <li><a class="dropdown-item" href="#">Another action</a></li>
                                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Faq Question section Start -->
    <section class="faq-contain">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="slider-4-2 product-wrapper">
                        <div>
                            <div class="faq-top-box">
                                <div class="faq-box-icon">
                                    <img src="{{ asset('fastkart-store/images/inner-page/faq/start.png') }}" class="blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="faq-box-contain">
                                    <h3>Getting Started</h3>
                                    <p>Bring to the table win-win survival strategies to ensure proactive domination.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="faq-top-box">
                                <div class="faq-box-icon">
                                    <img src="{{ asset('fastkart-store/images/inner-page/faq/help.png') }}" class="blur-up lazyload" alt="">
                                </div>

                                <div class="faq-box-contain">
                                    <h3>Sales Question</h3>
                                    <p>Lorizzle ipsizzle boom shackalack sit get down get down.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="faq-top-box">
                                <div class="faq-box-icon">
                                    <img src="{{ asset('fastkart-store/images/inner-page/faq/price.png') }}" class="blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="faq-box-contain">
                                    <h3>Pricing & Plans</h3>
                                    <p>Curabitizzle fizzle break yo neck, yall quis fo shizzle mah nizzle fo rizzle.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="faq-top-box">
                                <div class="faq-box-icon">
                                    <img src="{{ asset('fastkart-store/images/inner-page/faq/contact.png') }}" class="blur-up lazyload"
                                        alt="">
                                </div>

                                <div class="faq-box-contain">
                                    <h3>Support Contact</h3>
                                    <p>Gizzle fo shizzle bow wow wow bizzle leo bibendizzle check out this.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Faq Question section End -->

    <!-- Faq Section Start -->
    <section class="faq-box-contain section-b-space">
        <div class="container">
            <div class="row">
                <div class="col-xl-5">
                    <div class="faq-contain">
                        <h2>Frequently Asked Questions</h2>
                        <p>We are answering most frequent questions. No worries if you not find exact one. You can find
                            out more by searching or continuing clicking button below or directly <a
                                href="{{ route('store.page', ['page'=>'contact-us']) }}" class="theme-color text-decoration-underline">contact our
                                support.</a></p>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="faq-accordion">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOne">
                                        What is Bawaskar Farmer Store and why was the name changed? <i
                                            class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>Bawaskar Farmer Store is leading the charge in transforming India's vast, unorganised
                                            grocery landscape through cutting-edge technology and innovation. Blinkit is
                                            India's largest and most convenient hyper-local delivery company, which
                                            enables you to order grocery, fruits & vegetables, and other daily essential
                                            products, directly via your mobile or web browser.</p>

                                        <p>To know the reason why we changed our brand name from Grofers to Bawaskar Farmer Store,
                                            read this <span class="fw-bold">blog post.</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTwo">
                                        How to remove the impurities of Graphene oxide? <i
                                            class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>Discover, Explore & Understanding The Product Description Maecenas
                                            ullamcorper eros libero, facilisis temper mi dapibus vel. Sed ut felis
                                            ligula. Pellentesque vestibulum, tellus id euismod aliquet, justo velit
                                            tincidunt justo, nec pulvinar tortor elit vitae urna.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseThree">
                                        How long will delivery take? <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>Discover, Explore & Understanding The Product Description Maecenas
                                            ullamcorper eros libero, facilisis tempor mi dapibus vel. Sed ut felis
                                            ligula. Pellentesque vestibulum, tellus id euismod aliquet, justo velit
                                            tincidunt justo, nec pulvinar tortor elit vitae urna.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseFour">
                                        How do I find my Windows Product key? <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>The product key is located inside the product packaging, on the
                                            receipt or confirmation page for a digital purchase or in a
                                            confirmation e-mail that shows you purchased Windows. If you
                                            purchased a digital copy from Microsoft Store, you can locate your
                                            product key in your Account under Digital Content.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseFive">
                                        I've downloaded an ISO file, now what? <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>You can use the ISO file to create bootable media for installation
                                            or recovery. You can also install Windows on your current device
                                            by opening the ISO file, selecting the Setup and following the
                                            instructions.</p>

                                        <p>To create bootable media such as a bootable USB drive or DVD, you
                                            will need an ISO burning or mounting software. We recommend always
                                            using a blank USB or blank DVD because contents may be deleted
                                            when creating a bootable image.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSix">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseSix">
                                        What's the difference between 32-bit and 64-bit versions of Windows? <i
                                            class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseSix" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>The terms 32-bit and 64-bit refer to the way a computer's
                                            processor (also called a CPU) handles information. The 64-bit
                                            version of Windows handles large amounts of random access memory
                                            (RAM) more effectively than a 32-bit system. Not all devices can
                                            run the 64-bit versions of Windows.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSeven">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseSeven">
                                        Questionnaire on online shopping behavior during COVID-19. <i
                                            class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseSeven" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>Bawaskar Farmer Store is leading the charge in transforming India's vast, unorganised
                                            grocery landscape through cutting-edge technology and innovation. Blinkit is
                                            India's largest and most convenient hyper-local delivery company, which
                                            enables you to order grocery, fruits & vegetables, and other daily essential
                                            products, directly via your mobile or web browser.</p>

                                        <p>To know the reason why we changed our brand name from Grofers to Bawaskar Farmer Store,
                                            read this <span class="fw-bold">blog post.</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingEight">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseEight">
                                        How Can I Get More Attention From Customers? <i
                                            class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseEight" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>There are many variations of passages of Lorem Ipsum available, but the
                                            majority have suffered alteration in some form, by injected humour, or
                                            randomised words which don't look even slightly believable. If you are going
                                            to use a passage of Lorem Ipsum, you need to be sure there isn't anything
                                            embarrassing hidden in the middle of text. </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingNine">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseNine">
                                        What is payment method? <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </h2>
                                <div id="collapseNine" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p>simply dummy text of the printing and typesetting industry. Lorem Ipsum has
                                            been the industry's standard dummy text ever since the 1500s, when an
                                            unknown printer took a galley of type and scrambled it to make a type
                                            specimen book. It has survived not only five centuries, but also the leap
                                            into electronic typesetting, remaining essentially unchanged. It was
                                            popularised in the 1960s with the release of Letraset sheets containing
                                            Lorem Ipsum passages, and more recently with desktop publishing software
                                            like Aldus PageMaker including versions of Lorem Ipsum</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Faq Section End -->

    <!-- Footer Section Start -->
    @include('store.partials.footer')
    <!-- Footer Section End -->

    <!-- Location Modal Start -->
    @include('store.partials.location-modal')
    <!-- Location Modal End -->

    <!-- Deal Box Modal Start -->
    @include('store.partials.deal-modal')
    <!-- Deal Box Modal End -->

    <!-- Tap to top and theme setting button start -->
    <div class="theme-option">
        <div class="setting-box">
            <button class="btn setting-button">
                <i class="fa-solid fa-gear"></i>
            </button>

            <div class="theme-setting-2">
                <div class="theme-box">
                    <ul>
                        <li>
                            <div class="setting-name">
                                <h4>Color</h4>
                            </div>
                            <div class="theme-setting-button color-picker">
                                <form class="form-control">
                                    <label for="colorPick" class="form-label mb-0">Theme Color</label>
                                    <input type="color" class="form-control form-control-color" id="colorPick"
                                        value="#0da487" title="Choose your color">
                                </form>
                            </div>
                        </li>

                        <li>
                            <div class="setting-name">
                                <h4>Dark</h4>
                            </div>
                            <div class="theme-setting-button">
                                <button class="btn btn-2 outline" id="darkButton">Dark</button>
                                <button class="btn btn-2 unline" id="lightButton">Light</button>
                            </div>
                        </li>

                        <li>
                            <div class="setting-name">
                                <h4>RTL</h4>
                            </div>
                            <div class="theme-setting-button rtl">
                                <button class="btn btn-2 rtl-unline">LTR</button>
                                <button class="btn btn-2 rtl-outline">RTL</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="back-to-top">
            <a id="back-to-top" href="#">
                <i class="fas fa-chevron-up"></i>
            </a>
        </div>
    </div>
    <!-- Tap to top and theme setting button end -->

    <!-- Bg overlay Start -->
    <div class="bg-overlay"></div>
    <!-- Bg overlay End -->

    <!-- latest jquery-->
    <script src="{{ asset('fastkart-store/js/jquery-3.6.0.min.js') }}"></script>

    <!-- jquery ui-->
    <script src="{{ asset('fastkart-store/js/jquery-ui.min.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/popper.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/bootstrap/bootstrap-notify.min.js') }}"></script>

    <!-- feather icon js-->
    <script src="{{ asset('fastkart-store/js/feather/feather.min.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/feather/feather-icon.js') }}"></script>

    <!-- Lazyload Js -->
    <script src="{{ asset('fastkart-store/js/lazysizes.min.js') }}"></script>

    <!-- Slick js-->
    <script src="{{ asset('fastkart-store/js/slick/slick.js') }}"></script>
    <script src="{{ asset('fastkart-store/js/slick/custom_slick.js') }}"></script>

    <!-- script js -->
    <script src="{{ asset('fastkart-store/js/script.js') }}"></script>

    @include('store.partials.wishlist-script')

    <!-- theme setting js -->
    <script src="{{ asset('fastkart-store/js/theme-setting.js') }}"></script>
</body>

</html>

