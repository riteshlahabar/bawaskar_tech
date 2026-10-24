@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.company-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $sections = [
                            'Company Profile' => [
                                ['company_name', 'Company Name', 'text', true],
                                ['logo', 'Company Logo', 'file', false],
                                ['short_intro', 'Short Introduction', 'textarea', false],
                                ['description', 'Full Description', 'textarea', false],
                            ],
                            'Contact & Registration' => [
                                ['address', 'Address', 'textarea', false],
                                ['phone', 'Phone', 'text', false],
                                ['whatsapp', 'WhatsApp Number', 'text', false],
                                ['email', 'Email', 'email', false],
                                ['website', 'Website', 'url', false],
                                ['map_embed_url', 'Map Embed URL', 'url', false],
                                ['gst_number', 'GST Number', 'text', false],
                                ['cin_number', 'CIN Number', 'text', false],
                            ],
                            'Management' => [
                                ['founder_name', 'Founder', 'text', false],
                                ['chairman_name', 'Chairman', 'text', false],
                                ['managing_director_name', 'Managing Director', 'text', false],
                            ],
                            'Bank / Account Details' => [
                                ['account_name', 'Account Holder Name', 'text', false],
                                ['bank_name', 'Bank Name', 'text', false],
                                ['bank_branch', 'Branch', 'text', false],
                                ['account_number', 'Account Number', 'text', false],
                                ['ifsc_code', 'IFSC Code', 'text', false],
                                ['upi_id', 'UPI ID', 'text', false],
                            ],
                            'Invoice & Document Design' => [
                                ['invoice_template', 'PDF Design', 'select', true],
                            ],
                            'Google & Social Links' => [
                                ['google_business_url', 'Google Business URL', 'url', false],
                                ['facebook_url', 'Facebook URL', 'url', false],
                                ['instagram_url', 'Instagram URL', 'url', false],
                                ['youtube_url', 'YouTube URL', 'url', false],
                            ],
                        ];

                        $sectionHelp = [
                            'Bank / Account Details' => 'Printed in the footer of every Sale Order, Proforma Invoice and Sale Invoice PDF, for both Customer and Dealer. Leave blank to hide the block.',
                            'Invoice & Document Design' => 'Applies to every Sale Order, Proforma Invoice and Sale Invoice PDF, for both Customer and Dealer.',
                        ];
                    @endphp

                    <div class="row g-3">
                        @foreach($sections as $section => $fields)
                            <div class="col-12">
                                <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">{{ $section }}</div>
                                @if(! empty($sectionHelp[$section]))
                                    <div class="form-text mt-1">{{ $sectionHelp[$section] }}</div>
                                @endif
                            </div>
                            @foreach($fields as [$name, $label, $type, $required])
                                <div class="{{ in_array($name, ['short_intro','description','address'], true) ? 'col-12' : 'col-md-6' }}">
                                    <label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
                                    @if($type === 'textarea')
                                        <textarea class="form-control @error($name)is-invalid @enderror" name="{{ $name }}" rows="{{ $name === 'description' ? 5 : 3 }}">{{ old($name, $setting->{$name}) }}</textarea>
                                    @elseif($type === 'select')
                                        @php $current = old($name, $setting->{$name} ?: \App\Models\CompanySetting::DEFAULT_INVOICE_TEMPLATE); @endphp
                                        <select class="form-select @error($name)is-invalid @enderror" name="{{ $name }}" @required($required)>
                                            @foreach(\App\Models\CompanySetting::INVOICE_TEMPLATES as $key => $label)
                                                <option value="{{ $key }}" @selected($current === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($type === 'file')
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}" accept="image/*">
                                        @if($setting->logo_url)
                                            <a href="{{ $setting->logo_url }}" target="_blank" class="d-inline-block mt-2"><img src="{{ $setting->logo_url }}" class="admin-gallery-preview-thumb" alt="Company logo"></a>
                                        @endif
                                    @else
                                        <input class="form-control @error($name)is-invalid @enderror" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $setting->{$name}) }}" @required($required)>
                                    @endif
                                    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save Company Information</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
