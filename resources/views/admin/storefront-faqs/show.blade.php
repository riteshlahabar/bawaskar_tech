@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);
    $paragraphs = $record->answerParagraphs();
    $categoryLabel = $record->category_label;
    $isActive = (bool) $record->is_active;
    $websiteUrl = route('store.page', array_filter(['page' => 'faq', 'faq_category' => $record->category]));
    $tiles = [
        ['label' => 'Category', 'value' => $categoryLabel !== '' ? $categoryLabel : 'All Questions', 'icon' => 'grid', 'tone' => 'primary'],
        ['label' => 'Sort Order', 'value' => (int) $record->sort_order, 'icon' => 'bar-chart-2', 'tone' => 'info'],
        ['label' => 'Status', 'value' => $isActive ? 'Active' : 'Inactive', 'icon' => $isActive ? 'check-circle' : 'slash', 'tone' => $isActive ? 'purple' : 'danger'],
        ['label' => 'Answer Length', 'value' => str_word_count(strip_tags((string) $record->answer)).' words', 'icon' => 'align-left', 'tone' => 'warning'],
    ];
    $details = [
        'Category Key' => filled($record->category) ? $record->category : '-',
        'Paragraphs' => count($paragraphs),
        'Created On' => $record->created_at?->format('d-m-Y h:i A') ?: '-',
        'Last Updated' => $record->updated_at?->format('d-m-Y h:i A') ?: '-',
    ];
@endphp

<div class="report-page faq-show-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="faq-show-mark"><i data-feather="help-circle"></i></span>
                    <div>
                        <span class="report-hero-section">{{ $module['singular'] }}</span>
                        <h4 class="report-hero-title">{{ $record->question }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $isActive ? 'badge-light-success' : 'badge-light-danger' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                            @if($categoryLabel !== '')<span><i data-feather="grid"></i>{{ $categoryLabel }}</span>@endif
                            <span><i data-feather="bar-chart-2"></i>Position {{ (int) $record->sort_order }}</span>
                            @if($record->updated_at)<span><i data-feather="clock"></i>{{ $record->updated_at->format('d-m-Y') }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-reset" href="{{ route($module['route'].'.index', $query) }}"><i data-feather="arrow-left"></i>Back</a>
                    <a class="btn report-reset" href="{{ $websiteUrl }}" target="_blank" rel="noopener"><i data-feather="external-link"></i>View on Website</a>
                    @if(($module['can_edit'] ?? true) && $can['edit'])
                        <a class="btn btn-theme report-apply" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], $query)) }}"><i data-feather="edit-2"></i>Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-xl-4">
        @foreach($tiles as $tile)
            <div class="col">
                <div class="main-tiles border-0 card-hover card o-hidden h-100 mb-0 report-tile report-tone-{{ $tile['tone'] }}">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body p-0">
                                <span class="report-tile-label">{{ $tile['label'] }}</span>
                                <h4 class="report-tile-value">{{ $tile['value'] }}</h4>
                            </div>
                            <div class="align-self-center text-center report-tile-icon">
                                <i data-feather="{{ $tile['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card report-table-card h-100 mb-0">
                <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="card-header-title d-flex align-items-center gap-2">
                        <span class="people-info-icon"><i data-feather="message-square"></i></span>
                        <h4>Answer</h4>
                    </div>
                    <span class="report-meta">Shown on the website FAQ page</span>
                </div>
                <div class="card-body pt-0">
                    <div class="faq-show-preview">
                        <div class="faq-show-question">
                            <span>{{ $record->question }}</span>
                            <i data-feather="chevron-down"></i>
                        </div>
                        <div class="faq-show-answer">
                            @forelse($paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <p class="text-muted mb-0">No answer text saved yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card report-table-card people-info-card h-100 mb-0">
                <div class="card-header border-0">
                    <div class="card-header-title d-flex align-items-center gap-2">
                        <span class="people-info-icon"><i data-feather="info"></i></span>
                        <h4>Details</h4>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="people-info-list">
                        @foreach($details as $label => $value)
                            <li>
                                <span class="people-info-label">{{ $label }}</span>
                                <span class="people-info-value">{{ $value }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="faq-show-note mb-0">
                        <i data-feather="alert-circle"></i>
                        Visitors can search this question and filter it by its category. Turn Active off to hide it without deleting it.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
