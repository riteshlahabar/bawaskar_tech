@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    // $fieldTree, $fieldViews and $fieldNodes are supplied by the view composer.
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'image', 'image_multiple', 'product_media_repeater'], true));
    $submenuQueryKeys = ['type', 'placement', 'section_key', 'row_title'];
    $fieldNames = collect($module['fields'] ?? [])->pluck('name')->filter()->values()->all();
    $optionAttributes = $optionAttributes ?? [];
@endphp
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                {{-- The admin layout already renders the validation summary for
                     every page, so this form must not repeat it. --}}

                <p class="admin-form-required-legend text-muted small mb-3">
                    Fields marked <span class="text-danger">*</span> are compulsory.
                </p>

                <form method="POST" action="{{ $record ? route($module['route'].'.update', array_merge([$record->getKey()], request()->only($submenuQueryKeys))) : route($module['route'].'.store', request()->only($submenuQueryKeys)) }}" @if($hasUpload) enctype="multipart/form-data" @endif>
                    @csrf
                    @if($record) @method('PUT') @endif

                    @foreach(request()->only($submenuQueryKeys) as $queryKey => $queryValue)
                        @if(is_scalar($queryValue) && ! in_array($queryKey, $fieldNames, true))
                            <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                        @endif
                    @endforeach

                    <div class="row g-3">
                        @foreach($fieldNodes as $node)
                            @continue(! $fieldTree->shouldRender($node['field'], (bool) $record))
                            @include('admin.shared.fields.field', ['field' => $node['field'], 'children' => $node['children']])
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




