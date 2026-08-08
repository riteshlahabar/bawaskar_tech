@extends('store.layouts.live')

@section('title', 'Store Login')

@section('content')
@php
    $redirectTo = request('redirect_to');
@endphp
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain"><h2>Login</h2></div></div></div></div>
</section>
<section class="log-in-section section-b-space">
    <div class="container-fluid-lg">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="review-box">
                    <div class="review-title"><h4>Customer Login</h4></div>
                    <form method="POST" action="{{ route('store.auth.login') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="role" value="customer">
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo ?: route('store.page', ['page' => 'user-dashboard']) }}">
                        <div class="col-12">
                            <label class="form-label">Email or Mobile</label>
                            <input type="text" class="form-control" name="login" value="{{ old('login') }}" placeholder="Enter email or mobile">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Enter password">
                        </div>
                        <div class="col-12"><button type="submit" class="btn btn-animation w-100">Login as Customer</button></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="review-box">
                    <div class="review-title"><h4>Dealer Login</h4></div>
                    <form method="POST" action="{{ route('store.auth.login') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="role" value="dealer">
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo ?: route('store.page', ['page' => 'user-dashboard']) }}">
                        <div class="col-12">
                            <label class="form-label">Email or Mobile</label>
                            <input type="text" class="form-control" name="login" value="{{ old('login') }}" placeholder="Enter email or mobile">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Enter password">
                        </div>
                        <div class="col-12"><button type="submit" class="btn btn-animation w-100">Login as Dealer</button></div>
                    </form>
                    <p class="text-content mt-3 mb-0">Dealer orders stay available after admin approval and use dealer pricing automatically.</p>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <p class="mb-0">Need a new account? <a href="{{ route('store.page', ['page' => 'sign-up']) }}">Create customer or dealer account</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
