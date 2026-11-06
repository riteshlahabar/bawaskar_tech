@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    // Every input this section type needs, and nothing else. The field set is
    // decided once, in HomepageSectionContentFields.
    $has = fn (string $field): bool => in_array($field, $sectionFields, true);
    $unlimited = $rowLimit === null;
@endphp

<link rel="stylesheet" href="{{ asset('fastkart-admin/css/select2.min.css') }}">

<div class="card admin-form-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h5 class="mb-1">{{ $record->title }} &mdash; Content</h5>
                <small class="text-muted">
                    {{ str($record->section_type)->replace('_', ' ')->title() }}
                    @if($record->layout_type) &middot; {{ str($record->layout_type)->replace('_', ' ')->title() }} @endif
                    &middot;
                    @if($unlimited)
                        add as many rows as you need
                    @elseif($record->usesContentRowProducts())
                        the deal card plus {{ $rowLimit - 1 }} products
                    @else
                        this layout renders {{ $rowLimit }} row(s)
                    @endif
                </small>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('admin.homepage-settings.show', $record->id) }}">Back</a>
            </div>
        </div>

        <p class="admin-form-required-legend text-muted small mb-3">
            Fields marked <span class="text-danger">*</span> are compulsory.
            @if($record->usesContentRowProducts())
                The first row is the Special Offer card beside the stack; the rest fill the stack in this order. Leave a row empty to show fewer products.
                The countdown over the Special Offer card is set on the section itself, under Edit Section.
            @elseif($has('product_id'))
                The button appears on the homepage only when a product is selected, and reads &ldquo;Shop Now&rdquo; unless you change Button Text.
            @endif
        </p>

        <form method="POST" action="{{ route('admin.homepage-settings.save-content', $record->id) }}" enctype="multipart/form-data" data-homepage-content-form>
            @csrf

            <div data-content-rows @if($record->usesContentRowProducts()) data-content-row-labels="product" @endif>
                @foreach($rows as $index => $row)
                    @include('admin.homepage-settings.partials.content-row', [
                        'row' => $row,
                        'index' => $index,
                        'section' => $record,
                        'has' => $has,
                        'unlimited' => $unlimited,
                        'fieldHelper' => $fieldHelper,
                        'productOptions' => $productOptions,
                    ])
                @endforeach
            </div>

            @if($unlimited)
                <template data-content-row-template>
                    @include('admin.homepage-settings.partials.content-row', [
                        'row' => new \App\Models\Catalog\ProductHomepageSectionItem,
                        'index' => '__INDEX__',
                        'section' => $record,
                        'has' => $has,
                        'unlimited' => $unlimited,
                        'fieldHelper' => $fieldHelper,
                        'productOptions' => $productOptions,
                    ])
                </template>

                <button type="button" class="btn btn-outline-primary" data-add-content-row>
                    <i class="fa-solid fa-plus me-1"></i>Add Row
                </button>
            @endif

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary" type="submit">Save Content</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.homepage-settings.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('fastkart-admin/js/select2.min.js') }}"></script>
<script src="{{ asset('admin-module-js/homepage/content-rows.js').'?v='.filemtime(public_path('admin-module-js/homepage/content-rows.js')) }}"></script>
@endsection
