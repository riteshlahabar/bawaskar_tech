@php
    $row = (array) ($row ?? []);
    $index = $index ?? '__INDEX__';
    $rowName = $name ?? 'rows';
@endphp
<div class="row g-2 align-items-end mb-2" data-key-value-row>
    <div class="col-md-5">
        <input class="form-control" type="text" maxlength="120" name="{{ $rowName }}[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="{{ $labelPlaceholder ?? 'Label' }}">
    </div>
    <div class="col-md-6">
        <input class="form-control" type="text" maxlength="255" name="{{ $rowName }}[{{ $index }}][value]" value="{{ $row['value'] ?? '' }}" placeholder="{{ $valuePlaceholder ?? 'Value' }}">
    </div>
    <div class="col-md-1">
        <button type="button" class="btn btn-outline-danger w-100" data-remove-key-value-row title="Remove row"><i class="iconoir-trash"></i></button>
    </div>
</div>
