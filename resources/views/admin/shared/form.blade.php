@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'image', 'image_multiple'], true));
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
                                <div class="col-12">
                                    <div class="d-flex flex-wrap align-items-center gap-2 border rounded px-3 py-2 bg-light">
                                        <button type="button" class="btn btn-sm btn-primary" data-product-auto-translate data-url="{{ route('admin.products.translate') }}">
                                            Auto Translate
                                        </button>
                                        <small class="text-muted">Uses Product Name and Description. You can edit every language before saving.</small>
                                        <span class="small text-muted d-none" data-product-auto-translate-status></span>
                                    </div>
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
                                        <textarea rows="{{ $field['rows'] ?? 4 }}" class="form-control @error($name)is-invalid @enderror" name="{{ $name }}" @required($isRequired)>{{ $value }}</textarea>
                                    @elseif($type === 'image_multiple')
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}[]" accept="image/*" multiple @required($isRequired && ! $record)>
                                        @if($record && method_exists($record, 'images') && $record->relationLoaded('images'))
                                            @php($galleryPreviewImages = $record->images->where('is_primary', false))
                                            @if($galleryPreviewImages->isNotEmpty())
                                                <div class="admin-gallery-remove-inputs" data-gallery-remove-inputs></div>
                                                <div class="admin-gallery-preview-list d-flex flex-wrap gap-2 mt-2" data-gallery-removal-list>
                                                    @foreach($galleryPreviewImages as $img)
                                                        <div class="admin-gallery-preview-item" data-gallery-preview-item>
                                                            <button type="button" class="admin-gallery-remove-btn" data-gallery-remove-button data-image-id="{{ $img->id }}" aria-label="Remove image">&times;</button>
                                                            <a href="{{ asset($img->path) }}" target="_blank">
                                                                <img src="{{ asset($img->path) }}" class="admin-gallery-preview-thumb" alt="Gallery image">
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    @elseif(in_array($type, ['file', 'image'], true))
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}" accept="{{ $field['accept'] ?? ($type === 'image' ? 'image/*' : '') }}" @required($isRequired && ! $record)>
                                        @if($value)
                                            <small class="text-muted d-block mt-1">
                                                Current file: <a href="{{ asset($value) }}" target="_blank">View</a>
                                            </small>
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


    function initGalleryImageRemoval() {
        document.querySelectorAll('[data-gallery-removal-list]').forEach(function (list) {
            var form = list.closest('form');
            var inputsHost = form ? form.querySelector('[data-gallery-remove-inputs]') : null;

            if (!form || !inputsHost) {
                return;
            }

            list.addEventListener('click', function (event) {
                var button = event.target.closest('[data-gallery-remove-button]');
                if (!button) {
                    return;
                }

                event.preventDefault();

                var item = button.closest('[data-gallery-preview-item]');
                var imageId = button.dataset.imageId || '';

                if (!item || !imageId) {
                    return;
                }

                var existingInput = Array.from(inputsHost.querySelectorAll('input[name="remove_gallery_image_ids[]"]')).find(function (input) {
                    return input.value === imageId;
                });

                if (!existingInput) {
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'remove_gallery_image_ids[]';
                    hiddenInput.value = imageId;
                    inputsHost.appendChild(hiddenInput);
                }

                item.remove();
            });
        });
    }
    function initProductAutoTranslate() {
        document.querySelectorAll('[data-product-auto-translate]').forEach(function (button) {
            button.addEventListener('click', function () {
                var form = button.closest('form');
                if (!form) {
                    return;
                }

                var status = form.querySelector('[data-product-auto-translate-status]');
                var token = form.querySelector('input[name="_token"]');
                var nameInput = form.elements.namedItem('name');
                var descriptionInput = form.elements.namedItem('description');
                var name = nameInput ? nameInput.value.trim() : '';
                var description = descriptionInput ? descriptionInput.value.trim() : '';

                if (!name) {
                    if (status) {
                        status.classList.remove('d-none', 'text-success');
                        status.classList.add('text-danger');
                        status.textContent = 'Enter Product Name first.';
                    }
                    return;
                }

                button.disabled = true;
                if (status) {
                    status.classList.remove('d-none', 'text-danger', 'text-success');
                    status.textContent = 'Translating...';
                }

                fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token ? token.value : ''
                    },
                    body: JSON.stringify({ name: name, description: description })
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok) {
                                throw payload;
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        var translations = payload.translations || {};
                        ['hi', 'mr', 'gu', 'kn', 'te'].forEach(function (locale) {
                            var localeTranslation = translations[locale] || {};
                            var nameField = form.elements.namedItem('translation_' + locale + '_name');
                            var descriptionField = form.elements.namedItem('translation_' + locale + '_description');

                            if (nameField && localeTranslation.name) {
                                nameField.value = localeTranslation.name;
                            }

                            if (descriptionField && localeTranslation.description) {
                                descriptionField.value = localeTranslation.description;
                            }
                        });

                        if (status) {
                            status.classList.remove('d-none', 'text-danger');
                            status.classList.add('text-success');
                            status.textContent = 'Translation filled. Review and save product.';
                        }
                    })
                    .catch(function (error) {
                        var message = error && (error.message || error.error) ? (error.message || error.error) : 'Auto translation failed. Enter translations manually.';
                        if (status) {
                            status.classList.remove('d-none', 'text-success');
                            status.classList.add('text-danger');
                            status.textContent = message;
                        }
                    })
                    .finally(function () {
                        button.disabled = false;
                    });
            });
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
            initGalleryImageRemoval();
            initProductAutoTranslate();
        });
    } else {
        initConditionalAdminFields();
        initGalleryImageRemoval();
        initProductAutoTranslate();
    }
})();
</script>
@endsection




