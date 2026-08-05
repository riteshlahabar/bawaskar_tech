@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'image'], true));
@endphp
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">

            <div class="card-body pt-3">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $record ? route($module['route'].'.update', $record->getKey()) : route($module['route'].'.store') }}" @if($hasUpload) enctype="multipart/form-data" @endif>
                    @csrf
                    @if($record) @method('PUT') @endif

                    <div class="row g-3">
                        @foreach($module['fields'] as $field)
                            @continue($field['display_only'] ?? false)
                            @php($name = $field['name'])
                            @php($type = $field['type'] ?? 'text')
                            @php($value = old($name, $formData[$name] ?? ($field['default'] ?? null)))

                            <div class="{{ $field['col'] ?? 'col-md-6' }}">
                                @if($type === 'checkbox')
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" class="form-check-input" name="{{ $name }}" value="1" id="{{ $name }}" @checked((bool) $value)>
                                        <label class="form-check-label" for="{{ $name }}">{{ $field['label'] }}</label>
                                    </div>
                                @else
                                    <label class="form-label">
                                        {{ $field['label'] }}
                                        @if(str_contains(implode('|', array_map('strval', (array) ($field['rules'] ?? []))), 'required'))
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @if($type === 'select')
                                        <select class="form-select @error($name)is-invalid @enderror" name="{{ $name }}" @required($field['required'] ?? false)>
                                            <option value="">Select {{ $field['label'] }}</option>
                                            @foreach($options[$name] ?? [] as $key => $label)
                                                <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($type === 'textarea')
                                        <textarea rows="{{ $field['rows'] ?? 4 }}" class="form-control @error($name)is-invalid @enderror" name="{{ $name }}">{{ $value }}</textarea>
                                    @elseif(in_array($type, ['file', 'image'], true))
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}" accept="{{ $field['accept'] ?? ($type === 'image' ? 'image/*' : '') }}">
                                        @if($value)
                                            <small class="text-muted d-block mt-1">
                                                Current file: <a href="{{ asset($value) }}" target="_blank">View</a>
                                            </small>
                                        @endif
                                    @else
                                        <input class="form-control @error($name)is-invalid @enderror" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $value }}" step="{{ $field['step'] ?? null }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                                    @endif

                                    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if(! empty($field['help']))<small class="text-muted">{{ $field['help'] }}</small>@endif
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route($module['route'].'.index', request()->only(['type','placement','section_key','row_title'])) }}">Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>{{ $record ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection