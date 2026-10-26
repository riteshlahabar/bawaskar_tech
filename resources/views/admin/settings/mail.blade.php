@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                {{-- success / error flashes are rendered by admin.layouts.app --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.mail-settings.update') }}">
                    @csrf
                    @method('PUT')

                    @php
                        $sections = [
                            'Sending' => [
                                ['is_enabled', 'Send Emails', 'checkbox', false],
                                ['mailer', 'Mail Method', 'select', true],
                            ],
                            'SMTP Server' => [
                                ['host', 'SMTP Host', 'text', false],
                                ['port', 'Port', 'number', false],
                                ['encryption', 'Encryption', 'select', true],
                                ['username', 'Username', 'text', false],
                                ['password', 'Password', 'password', false],
                            ],
                            'Sender' => [
                                ['from_address', 'From Address', 'email', true],
                                ['from_name', 'From Name', 'text', false],
                                ['reply_to_address', 'Reply-To Address', 'email', false],
                            ],
                        ];

                        $options = [
                            'mailer' => \App\Models\MailSetting::MAILERS,
                            'encryption' => \App\Models\MailSetting::ENCRYPTIONS,
                        ];

                        $sectionHelp = [
                            'Sending' => 'Turn this off to stop every email at once. Which emails go out, and their wording, is set in Settings → Email Templates.',
                            'SMTP Server' => 'Use the mail account details from cPanel → Email Accounts → Connect Devices. Required only for the SMTP method.',
                            'Sender' => 'The From Address should be a real mailbox on this domain, or the mail may be treated as spam.',
                        ];

                        $fieldHelp = [
                            'port' => 'Leave blank to use 587 for TLS or 465 for SSL.',
                            'password' => 'Stored encrypted. Leave blank to keep the saved password.',
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
                                <div class="{{ $type === 'checkbox' ? 'col-12' : 'col-md-6' }}">
                                    @if($type === 'checkbox')
                                        <div class="form-check">
                                            <input type="hidden" name="{{ $name }}" value="0">
                                            <input class="form-check-input" type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked(old($name, $setting->{$name}))>
                                            <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
                                        </div>
                                    @else
                                        <label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
                                        @if($type === 'select')
                                            @php $current = old($name, $setting->{$name}); @endphp
                                            <select class="form-select @error($name)is-invalid @enderror" name="{{ $name }}" @required($required)>
                                                @foreach($options[$name] as $key => $optionLabel)
                                                    <option value="{{ $key }}" @selected((string) $current === (string) $key)>{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($type === 'password')
                                            <input class="form-control @error($name)is-invalid @enderror" type="password" name="{{ $name }}" value="" autocomplete="new-password">
                                        @else
                                            <input class="form-control @error($name)is-invalid @enderror" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $setting->{$name}) }}" @required($required)>
                                        @endif
                                    @endif
                                    @if(! empty($fieldHelp[$name]))<div class="form-text">{{ $fieldHelp[$name] }}</div>@endif
                                    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save Mail Settings</button>
                    </div>
                </form>

                <hr class="my-4">

                <form method="POST" action="{{ route('admin.mail-settings.test') }}">
                    @csrf
                    <div class="admin-form-section-heading border rounded px-3 py-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">Send Test Email</div>
                    <div class="form-text mt-1 mb-2">Save the settings first, then send yourself a test message to confirm they work.</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Send To</label>
                            <input class="form-control" type="email" name="test_email" value="{{ old('test_email', auth()->user()?->email) }}" required>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100" type="submit"><i class="iconoir-send me-1"></i>Send Test Email</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
