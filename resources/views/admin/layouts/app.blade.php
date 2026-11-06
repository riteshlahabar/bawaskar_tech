<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bawaskar Farmer Medicine ERP, eCommerce and Salesman HRMS">
    <title>@yield('title', $pageTitle ?? 'Admin') | {{ config('admin.brand.name') }}</title>
    <link rel="icon" href="{{ asset('logo/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/ratio.css') }}">
    {{-- The template's other icon stylesheets are deliberately NOT loaded:
         remixicon.css and vendors/themify.css would pull ~2.7MB of fonts that
         nothing in this admin uses, vendors/feather-icon.css is only
         normalize.css under an icon name, and vendors/font-awesome.css is
         Font Awesome 5, which would fight the CDN's Font Awesome 6 above over
         the same .fa-* classes. Every admin icon is Font Awesome 6 or a
         feather SVG drawn by feather.min.js. --}}
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/vendors/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/vendors/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/bawaskar-fastkart.css').'?v='.filemtime(public_path('fastkart-admin/css/bawaskar-fastkart.css')) }}">
    @stack('styles')
</head>
<body>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    @include('admin.partials.topbar')
    <div class="page-body-wrapper">
        @include('admin.partials.startbar')
        <div class="page-body">
            <div class="container-fluid">
                @include('admin.partials.page-title')
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</div>
<script src="{{ asset('fastkart-admin/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/icons/feather-icon/feather.min.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/icons/feather-icon/feather-icon.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/scrollbar/simplebar.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/scrollbar/custom.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/config.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/sidebar-menu.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/sidebareffect.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/tooltip-init.js') }}"></script>
<script src="{{ asset('fastkart-admin/js/script.js') }}"></script>
<script src="{{ asset('admin-module-js/shared/sidebar-active.js').'?v='.filemtime(public_path('admin-module-js/shared/sidebar-active.js')) }}"></script>
<script src="{{ asset('admin-module-js/shared/table-toolbar.js').'?v='.filemtime(public_path('admin-module-js/shared/table-toolbar.js')) }}"></script>
@stack('scripts')
</body>
</html>
