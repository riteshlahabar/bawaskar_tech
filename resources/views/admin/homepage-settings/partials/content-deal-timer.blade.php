@php
    // The Special Offer countdown. It belongs to the SECTION, not to this row,
    // so these three inputs post as `deal[...]` outside the `rows[]` array -
    // but they are rendered here because row 1 IS the deal card the countdown
    // runs over, and that is where the admin looks for it.
    $dealOld = fn (string $field, $fallback = null) => old('deal.'.$field, $fallback);
    $dealLocal = fn ($value): ?string => $value ? $value->format('Y-m-d\TH:i') : null;
@endphp

<div class="border-top mt-3 pt-3">
    <strong class="text-dark d-block">Offer Countdown</strong>
    <small class="text-muted d-block mb-3">Shown over this Special Offer card on the homepage. Needs the end date to appear.</small>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="deal_starts_at">Offer Start Date &amp; Time</label>
            <input type="datetime-local" id="deal_starts_at" name="deal[starts_at]" value="{{ $dealOld('starts_at', $dealLocal($section->deal_starts_at)) }}" class="form-control @error('deal.starts_at') is-invalid @enderror">
            @error('deal.starts_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <small class="text-muted d-block mt-1">Leave empty to start the countdown immediately.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="deal_ends_at">Offer End Date &amp; Time</label>
            <input type="datetime-local" id="deal_ends_at" name="deal[ends_at]" value="{{ $dealOld('ends_at', $dealLocal($section->deal_ends_at)) }}" class="form-control @error('deal.ends_at') is-invalid @enderror">
            @error('deal.ends_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <small class="text-muted d-block mt-1">The countdown needs this date. Without it the card shows with no timer.</small>
        </div>

        <div class="col-md-4">
            <div class="form-check form-switch mt-md-4">
                <input type="checkbox" class="form-check-input" id="deal_timer_active" name="deal[timer_active]" value="1" @checked((bool) $dealOld('timer_active', $section->deal_timer_active))>
                <label class="form-check-label" for="deal_timer_active">Offer Timer Active</label>
            </div>
            @error('deal.timer_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <small class="text-muted d-block mt-1">Shows the countdown over the card until the end date passes.</small>
        </div>
    </div>
</div>
