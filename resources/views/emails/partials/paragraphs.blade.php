{{--
    The admin-edited wording. It is plain text, split on blank lines, and
    printed through Blade's escaping - a typed-in template can change the
    words but can never inject markup or break the layout.
--}}
@foreach ($paragraphs as $paragraph)
    <p style="margin:0 0 14px;">{!! nl2br(e($paragraph)) !!}</p>
@endforeach
