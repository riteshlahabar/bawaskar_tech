@php
    // One content row. `$index` is '__INDEX__' inside the + template; the JS
    // swaps it for the next number before the row is inserted.
    $name = fn (string $field): string => 'rows['.$index.']['.$field.']';
    $errorKey = fn (string $field): string => 'rows.'.$index.'.'.$field;
    $old = fn (string $field, $fallback = null) => old('rows.'.$index.'.'.$field, $fallback);
    $rowNumber = is_numeric($index) ? ((int) $index + 1) : null;
    $videoSource = $old('video_source', filled($row->video_file_path) && blank($row->video_url) ? 'upload' : 'url');
@endphp

<div class="border rounded p-3 mb-3" data-content-row>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <strong class="text-dark" data-content-row-label>{{ $fieldHelper->rowLabel($section, $rowNumber) }}</strong>
        @if($unlimited)
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-content-row title="Remove this row">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        @endif
    </div>

    <input type="hidden" name="{{ $name('id') }}" value="{{ $row->id }}">
    {{-- Set by the Remove button; an existing row is deleted, a new one is just dropped. --}}
    <input type="hidden" name="{{ $name('_delete') }}" value="" data-content-row-delete>

    <div class="row g-3">
        @foreach(['image_path', 'logo_image_path'] as $imageField)
            @if($has($imageField))
                <div class="col-md-6">
                    <label class="form-label">
                        {{ $fieldHelper->label($imageField) }}
                        @if(blank($row->{$imageField}))<span class="text-danger" title="Required field">*</span>@endif
                    </label>
                    {{-- Tells the validator an image is already stored, so saving
                         without choosing a new file keeps the current one. --}}
                    <input type="hidden" name="{{ $name($imageField.'_existing') }}" value="{{ $row->{$imageField} }}">
                    <input type="file" name="{{ $name($imageField) }}" accept="image/jpeg,image/png,image/webp" class="form-control @error($errorKey($imageField)) is-invalid @enderror">
                    @error($errorKey($imageField))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @if(filled($row->{$imageField}))
                        <div class="mt-2">
                            <img src="{{ asset($row->{$imageField}) }}" alt="Current image" class="admin-image-thumb" style="max-height:60px">
                            <small class="text-muted d-block">Leave empty to keep this image.</small>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach

        @if($has('product_id'))
            <div class="col-md-6">
                <label class="form-label">{{ $fieldHelper->label('product_id') }}</label>
                <select name="{{ $name('product_id') }}" class="form-select @error($errorKey('product_id')) is-invalid @enderror" data-product-select>
                    <option value="">{{ $section->usesContentRowProducts() ? 'Empty - show nothing here' : 'No product (no button)' }}</option>
                    @foreach($productOptions as $productId => $productLabel)
                        <option value="{{ $productId }}" @selected((string) $old('product_id', $row->product_id) === (string) $productId)>{{ $productLabel }}</option>
                    @endforeach
                </select>
                @error($errorKey('product_id'))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        @endif

        @if($has('button_text'))
            <div class="col-md-6">
                <label class="form-label">{{ $fieldHelper->label('button_text') }}</label>
                <input type="text" name="{{ $name('button_text') }}" value="{{ $old('button_text', $row->button_text) }}" maxlength="80" placeholder="Shop Now" class="form-control @error($errorKey('button_text')) is-invalid @enderror">
                @error($errorKey('button_text'))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        @endif

        @if($has('icon_key'))
            <div class="col-md-6">
                <label class="form-label">{{ $fieldHelper->label('icon_key') }} <span class="text-danger" title="Required field">*</span></label>
                <select name="{{ $name('icon_key') }}" class="form-select @error($errorKey('icon_key')) is-invalid @enderror" required>
                    <option value="">Select an icon</option>
                    @foreach(\App\Services\Catalog\Homepage\HomepageSectionContentFields::SERVICE_ICONS as $iconKey => $iconLabel)
                        <option value="{{ $iconKey }}" @selected((string) $old('icon_key', $row->icon_key) === (string) $iconKey)>{{ $iconLabel }}</option>
                    @endforeach
                </select>
                @error($errorKey('icon_key'))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        @endif

        @foreach(['title' => 255, 'subtitle' => 255, 'coupon_code' => 80, 'discount_text' => 255, 'validity_text' => 255] as $textField => $maxLength)
            @if($has($textField))
                @php($textRequired = in_array($textField, ['title', 'coupon_code'], true))
                <div class="col-md-6">
                    <label class="form-label">
                        {{ $fieldHelper->label($textField) }}
                        @if($textRequired)<span class="text-danger" title="Required field">*</span>@endif
                    </label>
                    <input type="text" name="{{ $name($textField) }}" value="{{ $old($textField, $row->{$textField}) }}" maxlength="{{ $maxLength }}" class="form-control @error($errorKey($textField)) is-invalid @enderror">
                    @error($errorKey($textField))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            @endif
        @endforeach

        @if($has('video_source'))
            <div class="col-md-6">
                <label class="form-label">{{ $fieldHelper->label('video_source') }} <span class="text-danger" title="Required field">*</span></label>
                <select name="{{ $name('video_source') }}" class="form-select" data-video-source>
                    @foreach(\App\Services\Catalog\Homepage\HomepageSectionContentFields::VIDEO_SOURCES as $sourceKey => $sourceLabel)
                        <option value="{{ $sourceKey }}" @selected((string) $videoSource === (string) $sourceKey)>{{ $sourceLabel }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Exactly one of the two is visible, so the row never asks for a
                 link and a file at the same time. --}}
            <div class="col-md-6" data-video-field="url" @style(['display:none' => $videoSource !== 'url'])>
                <label class="form-label">{{ $fieldHelper->label('video_url') }} <span class="text-danger" title="Required field">*</span></label>
                <input type="text" name="{{ $name('video_url') }}" value="{{ $old('video_url', $row->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="form-control @error($errorKey('video_url')) is-invalid @enderror">
                @error($errorKey('video_url'))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6" data-video-field="upload" @style(['display:none' => $videoSource !== 'upload'])>
                <label class="form-label">
                    {{ $fieldHelper->label('video_file_path') }}
                    @if(blank($row->video_file_path))<span class="text-danger" title="Required field">*</span>@endif
                </label>
                <input type="hidden" name="{{ $name('video_file_path_existing') }}" value="{{ $row->video_file_path }}">
                <input type="file" name="{{ $name('video_file_path') }}" accept="video/mp4,video/webm,video/ogg" class="form-control @error($errorKey('video_file_path')) is-invalid @enderror">
                @error($errorKey('video_file_path'))<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @if(filled($row->video_file_path))
                    <small class="text-muted d-block mt-1">Current file: {{ basename($row->video_file_path) }}. Leave empty to keep it.</small>
                @endif
            </div>
        @endif
    </div>

    {{-- Row 1 of Top Selling is the Special Offer card, so its countdown is
         edited right here instead of on the section form. --}}
    @if($rowNumber === 1 && $section->usesContentRowProducts())
        @include('admin.homepage-settings.partials.content-deal-timer', ['section' => $section])
    @endif
</div>
