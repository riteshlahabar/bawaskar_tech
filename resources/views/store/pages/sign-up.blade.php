@extends('store.layouts.live')

@section('title', 'Create Account')

@section('content')
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg"><div class="row"><div class="col-12"><div class="breadcrumb-contain"><h2>Create Account</h2></div></div></div></div>
</section>
<section class="log-in-section section-b-space">
    <div class="container-fluid-lg">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="review-box">
                    <div class="review-title"><h4>Customer Registration</h4></div>
                    <form method="POST" action="{{ route('store.auth.register') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="role" value="customer">
                        <div class="col-12"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="{{ old('name') }}"></div>
                        <div class="col-12"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}"></div>
                        <div class="col-12"><label class="form-label">Mobile</label><input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}"></div>
                        <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password"></div>
                        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="password_confirmation"></div>
                        <div class="col-12"><button type="submit" class="btn btn-animation w-100">Create Customer Account</button></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="review-box">
                    <div class="review-title"><h4>Dealer Registration</h4></div>
                    <form method="POST" action="{{ route('store.auth.register') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="role" value="dealer">
                        <div class="col-12"><label class="form-label">Contact Name</label><input type="text" class="form-control" name="name" value="{{ old('name') }}"></div>
                        <div class="col-12"><label class="form-label">Firm Name</label><input type="text" class="form-control" name="firm_name" value="{{ old('firm_name') }}"></div>
                        <div class="col-12"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}"></div>
                        <div class="col-12"><label class="form-label">Mobile</label><input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}"></div>
                        <div class="col-12"><label class="form-label">GST Number</label><input type="text" class="form-control" name="gst_number" value="{{ old('gst_number') }}"></div>
                        <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password"></div>
                        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="password_confirmation"></div>
                        <div class="col-12"><button type="submit" class="btn btn-animation w-100">Submit Dealer Registration</button></div>
                    </form>
                    <p class="text-content mt-3 mb-0">Dealer login becomes available after admin approval.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
