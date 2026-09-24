@php
    $listName = $field['name'];
    $checked = array_map('strval', (array) old($listName, $formData[$listName] ?? ($field['default'] ?? [])));
    // A static list stays in config; a list built from a model arrives
    // already resolved in $options, the same way a select's does.
    $listOptions = $field['options'] ?? (($options ?? [])[$listName] ?? []);
    $isScrollable = count($listOptions) > ($field['scroll_after'] ?? 12);
@endphp
<label class="form-label">{{ $field['label'] }}</label>
@if($field['clearable'] ?? false)
    {{-- An all-unchecked list submits no key at all, so `validated()` would
         never carry it and the stored value could not be emptied. This blank
         entry keeps the key present; the model filters it out. --}}
    <input type="hidden" name="{{ $listName }}[]" value="">
@endif
<div class="d-flex flex-wrap gap-3 @if($isScrollable) border rounded p-2 overflow-auto @endif" @if($isScrollable) style="max-height:220px;" @endif>
    @forelse($listOptions as $optionValue => $optionLabel)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="{{ $listName }}[]" id="{{ $listName }}-{{ $optionValue }}" value="{{ $optionValue }}" @checked(in_array((string) $optionValue, $checked, true))>
            <label class="form-check-label" for="{{ $listName }}-{{ $optionValue }}">{{ $optionLabel }}</label>
        </div>
    @empty
        <span class="text-muted small">Nothing to choose from yet.</span>
    @endforelse
</div>
@if(! empty($field['help']))<small class="text-muted d-block">{{ $field['help'] }}</small>@endif
