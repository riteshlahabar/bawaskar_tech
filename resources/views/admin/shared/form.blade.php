@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'image', 'image_multiple', 'product_media_repeater'], true));
    $submenuQueryKeys = ['type', 'placement', 'section_key', 'row_title'];
    $fieldNames = collect($module['fields'] ?? [])->pluck('name')->filter()->values()->all();
    $optionAttributes = $optionAttributes ?? [];
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

                <form method="POST" action="{{ $record ? route($module['route'].'.update', array_merge([$record->getKey()], request()->only($submenuQueryKeys))) : route($module['route'].'.store', request()->only($submenuQueryKeys)) }}" @if($hasUpload) enctype="multipart/form-data" @endif>
                    @csrf
                    @if($record) @method('PUT') @endif

                    @foreach(request()->only($submenuQueryKeys) as $queryKey => $queryValue)
                        @if(is_scalar($queryValue) && ! in_array($queryKey, $fieldNames, true))
                            <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                        @endif
                    @endforeach

                    <div class="row g-3">
                        @foreach($module['fields'] as $field)
                            @continue(($field['display_only'] ?? false) || (($field['create_only'] ?? false) && $record) || (($field['edit_only'] ?? false) && ! $record))
                            @php($type = $field['type'] ?? 'text')
                            @php($visibilitySource = $field['visibility_field'] ?? null)
                            @php($visibilitySectionTypes = array_values(array_filter((array) ($field['show_for_section_types'] ?? []))))
                            @php($visibilityLayoutTypes = array_values(array_filter((array) ($field['show_for_layout_types'] ?? []))))
                            @php($hasVisibility = filled($visibilitySource) && ($visibilitySectionTypes !== [] || $visibilityLayoutTypes !== []))

                            @if($type === 'section_heading')
                                <div class="col-12 {{ $hasVisibility ? 'admin-conditional-field' : '' }}"
                                    @if($hasVisibility)
                                        data-visibility-source="{{ $visibilitySource }}"
                                        data-visibility-section-types="{{ implode(',', $visibilitySectionTypes) }}"
                                        data-visibility-layout-types="{{ implode(',', $visibilityLayoutTypes) }}"
                                        style="display:none;"
                                    @endif>
                                    <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">{{ $field['label'] }}</div>
                                </div>
                                @continue
                            @endif


                            @if($type === 'product_translation_tools')
                                @include('admin.products.partials.translation-tools')
                                @continue
                            @endif

                            @if($type === 'product_variants_repeater')
                                <div class="col-12">
                                    @include('admin.products.partials.variants.repeater')
                                    @error('variants')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                @continue
                            @endif

                            @if($type === 'product_media_repeater')
                                <div class="col-12">
                                    @include('admin.products.partials.media.repeater')
                                    @error('media')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                @continue
                            @endif
                            @continue(empty($field['name']))
                            @php($name = $field['name'] ?? '')
                            @php($value = old($name, $formData[$name] ?? ($field['default'] ?? null)))
                            @php($rulesList = array_map('strval', (array) ($field['rules'] ?? [])))
                            @php($hasRequiredRule = collect($rulesList)->contains(fn ($rule) => str_starts_with($rule, 'required')))
                            @php($hasConditionalRequiredRule = collect($rulesList)->contains(fn ($rule) => str_starts_with($rule, 'required_with') || str_starts_with($rule, 'required_if') || str_starts_with($rule, 'required_without')))
                            @php($forcedRequiredIndicator = (bool) ($field['force_required_indicator'] ?? false))
                            @php($isRequired = $hasRequiredRule || $forcedRequiredIndicator)
                            @php($requiredIndicator = '*')

                            @if($type === 'hidden')
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @continue
                            @endif

                            @php($lockedBySubmenu = in_array($module['key'] ?? '', ['storefront-banners', 'storefront-sections'], true) && in_array($name, ['placement', 'section_key'], true) && request()->filled($name))

                            @if($lockedBySubmenu)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @continue
                            @endif

                            <div class="{{ $field['col'] ?? 'col-md-6' }} {{ $hasVisibility ? 'admin-conditional-field' : '' }}"
                                @if($hasVisibility)
                                    data-visibility-source="{{ $visibilitySource }}"
                                    data-visibility-section-types="{{ implode(',', $visibilitySectionTypes) }}"
                                    data-visibility-layout-types="{{ implode(',', $visibilityLayoutTypes) }}"
                                    style="display:none;"
                                @endif>
                                @if($type === 'checkbox')
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" class="form-check-input" name="{{ $name }}" value="1" id="{{ $name }}" @checked((bool) $value)>
                                        <label class="form-check-label" for="{{ $name }}">{{ $field['label'] }}</label>
                                        @error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                        @if(! empty($field['help']))<small class="text-muted d-block mt-1">{{ $field['help'] }}</small>@endif
                                    </div>
                                @else
                                    <label class="form-label">
                                        {{ $field['label'] }}
                                        @if($isRequired)
                                            <span class="text-danger" title="Required field">{{ $requiredIndicator }}</span>
                                        @endif
                                    </label>

                                    @if($type === 'select')
                                        <select class="form-select @error($name)is-invalid @enderror" name="{{ $name }}" id="{{ $name }}" data-option-attributes='@json($optionAttributes[$name] ?? [])' @required($isRequired)>
                                            <option value="">Select {{ $field['label'] }}</option>
                                            @foreach($options[$name] ?? [] as $key => $label)
                                                @php($attrs = $optionAttributes[$name][$key] ?? [])
                                                <option value="{{ $key }}" @selected((string) $value === (string) $key)
                                                    @foreach($attrs as $attrName => $attrValue)
                                                        @if($attrValue !== null && $attrValue !== '')
                                                            data-{{ \Illuminate\Support\Str::kebab($attrName) }}="{{ $attrValue }}"
                                                        @endif
                                                    @endforeach>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($type === 'textarea')
                                        <textarea rows="{{ $field['rows'] ?? 4 }}" class="form-control @error($name)is-invalid @enderror" name="{{ $name }}" @if(! empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif @if(! empty($field['character_counter'])) data-character-counter @endif @required($isRequired)>{{ $value }}</textarea>
                                        @if(! empty($field['character_counter']))<small class="text-muted d-block"><span data-character-count>0</span> / {{ $field['maxlength'] ?? 160 }}</small>@endif
                                    @elseif($type === 'image_multiple')
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}[]" accept="image/*" multiple @required($isRequired && ! $record)>

                                        @if(($module['key'] ?? '') === 'products')
                                            @include('admin.products.partials.images.gallery-preview')
                                        @endif

                                    @elseif(in_array($type, ['file', 'image'], true))
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}" accept="{{ $field['accept'] ?? ($type === 'image' ? 'image/*' : '') }}" @required($isRequired && ! $record)>

                                        @if($value)

                                            @if(($module['key'] ?? '') === 'products')

                                                @include('admin.products.partials.images.current-preview')

                                            @else

                                                <div class="admin-gallery-preview-list d-flex flex-wrap gap-2 mt-2">
                                                    <div class="admin-gallery-preview-item">
                                                        <a href="{{ asset($value) }}" target="_blank">
                                                            <img
                                                                src="{{ asset($value) }}"
                                                                class="admin-gallery-preview-thumb"
                                                                alt="Current image"
                                                            >
                                                        </a>
                                                    </div>
                                                </div>

                                            @endif

                                        @endif
                                    @else
                                        <input class="form-control @error($name)is-invalid @enderror" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $value }}" @required($isRequired) step="{{ $field['step'] ?? null }}" placeholder="{{ $field['placeholder'] ?? '' }}">
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

<script>
(function () {
    function syncConditionalFields(form) {
        var conditionalBlocks = form.querySelectorAll('.admin-conditional-field[data-visibility-source]');
        if (!conditionalBlocks.length) {
            return;
        }

        conditionalBlocks.forEach(function (block) {
            var sourceName = block.dataset.visibilitySource;
            var source = form.elements.namedItem(sourceName);
            if (!source || !('options' in source)) {
                return;
            }

            var selectedOption = source.options[source.selectedIndex] || null;
            var selectedValue = source.value || '';
            var optionAttributeMap = {};

            if (source.dataset.optionAttributes) {
                try {
                    optionAttributeMap = JSON.parse(source.dataset.optionAttributes);
                } catch (error) {
                    optionAttributeMap = {};
                }
            }

            var selectedOptionAttributes = selectedValue && optionAttributeMap[selectedValue] ? optionAttributeMap[selectedValue] : {};
            var sectionType = selectedOption ? (selectedOption.dataset.sectionType || '') : '';
            var layoutType = selectedOption ? (selectedOption.dataset.layoutType || '') : '';

            if (!sectionType && selectedOptionAttributes.section_type) {
                sectionType = selectedOptionAttributes.section_type;
            }

            if (!layoutType && selectedOptionAttributes.layout_type) {
                layoutType = selectedOptionAttributes.layout_type;
            }

            var allowedSectionTypes = (block.dataset.visibilitySectionTypes || '').split(',').map(function (value) {
                return value.trim();
            }).filter(Boolean);
            var allowedLayoutTypes = (block.dataset.visibilityLayoutTypes || '').split(',').map(function (value) {
                return value.trim();
            }).filter(Boolean);
            var isVisible = Boolean(selectedValue);

            if (isVisible && allowedSectionTypes.length) {
                isVisible = allowedSectionTypes.indexOf(sectionType) !== -1;
            }

            if (isVisible && allowedLayoutTypes.length) {
                isVisible = allowedLayoutTypes.indexOf(layoutType) !== -1;
            }

            block.style.display = isVisible ? '' : 'none';

            block.querySelectorAll('input, select, textarea').forEach(function (control) {
                if (!control.dataset.conditionalOriginalDisabled) {
                    control.dataset.conditionalOriginalDisabled = control.disabled ? '1' : '0';
                }

                control.disabled = isVisible ? control.dataset.conditionalOriginalDisabled === '1' : true;
            });
        });
    }


    function initCharacterCounters() {
        document.querySelectorAll('[data-character-counter]').forEach(function (field) {
            var target = field.parentElement.querySelector('[data-character-count]');
            var sync = function () {
                if (target) target.textContent = String(field.value.length);
            };
            field.addEventListener('input', sync);
            sync();
        });
    }
    function initConditionalAdminFields() {
        document.querySelectorAll('.admin-form-card form').forEach(function (form) {
            var conditionalBlocks = form.querySelectorAll('.admin-conditional-field[data-visibility-source]');
            if (!conditionalBlocks.length) {
                return;
            }

            Array.from(new Set(Array.from(conditionalBlocks).map(function (block) {
                return block.dataset.visibilitySource;
            }))).forEach(function (sourceName) {
                var source = form.elements.namedItem(sourceName);
                if (source) {
                    source.addEventListener('change', function () {
                        syncConditionalFields(form);
                    });
                }
            });

            syncConditionalFields(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initConditionalAdminFields();
            initCharacterCounters();
        });
    } else {
        initConditionalAdminFields();
        initCharacterCounters();
    }
})();
</script>

@if(($module['key'] ?? '') === 'products')
    @include('admin.products.partials.scripts')
@endif
@endsection




