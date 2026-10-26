@extends('emails.layout')

{{--
    Admin-only preview of one template's wording, opened from the Email
    Templates list. Sample values stand in for the placeholders; the data
    block a real send adds underneath is not shown, because it is built from
    an actual invoice or order.
--}}

@section('content')
    <div style="padding:10px 12px;margin-bottom:18px;background-color:#fff8e1;border:1px solid #f0d9a0;font-size:12px;color:#7a5d1a;">
        Preview of <strong>{{ $template->name }}</strong> with sample values. Subject: <strong>{{ $subjectLine }}</strong>.
        A real send also prints the figures below this text.
    </div>

    @include('emails.partials.paragraphs')
@endsection
