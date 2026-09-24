{{-- A repeatable label/value table for any module, stored in one text column
     via the KeyValueRows cast. The product "Additional Information" block has
     its own copy under admin/products/partials because its JS is bundled with
     the variant and media repeaters; this is the shared one. --}}
@php
    $rowName = $field['name'];
    $rowsValue = old($rowName, $formData[$rowName] ?? []);
    $rowsValue = is_array($rowsValue) ? array_values($rowsValue) : [];

    // Always render one blank row, so the field is usable without first
    // pressing Add.
    if ($rowsValue === []) {
        $rowsValue = [[]];
    }

    $labelPlaceholder = $field['label_placeholder'] ?? 'Label';
    $valuePlaceholder = $field['value_placeholder'] ?? 'Value';
@endphp
<label class="form-label">{{ $field['label'] }}</label>
<div data-key-value-repeater>
    <div data-key-value-rows>
        @foreach($rowsValue as $rowIndex => $rowValue)
            @include('admin.shared.fields.key-value-repeater-row', ['row' => $rowValue, 'index' => $rowIndex, 'name' => $rowName, 'labelPlaceholder' => $labelPlaceholder, 'valuePlaceholder' => $valuePlaceholder])
        @endforeach
    </div>
    <template data-key-value-template>
        @include('admin.shared.fields.key-value-repeater-row', ['row' => [], 'index' => '__INDEX__', 'name' => $rowName, 'labelPlaceholder' => $labelPlaceholder, 'valuePlaceholder' => $valuePlaceholder])
    </template>
    {{-- An emptied repeater submits no key at all, so validated() would never
         carry it and the stored value could not be cleared. This blank entry
         keeps the key present; the KeyValueRows cast drops it. --}}
    <input type="hidden" name="{{ $rowName }}[__blank__][label]" value="">
    <input type="hidden" name="{{ $rowName }}[__blank__][value]" value="">
    <button type="button" class="btn btn-outline-primary btn-sm" data-add-key-value-row><i class="iconoir-plus me-1"></i>Add Another Row</button>
    @if(! empty($field['help']))<small class="text-muted d-block mt-2">{{ $field['help'] }}</small>@endif
</div>
@once
    {{-- Inline like the location picker's own script, rather than pushed to
         the layout's stack: the same convention as the other shared fields. --}}
    <script src="{{ asset('js/key-value-repeater.js') }}" defer></script>
@endonce
