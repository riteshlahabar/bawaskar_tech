@extends('emails.layout')

@section('content')
    <p style="margin:0 0 14px;">Your mail settings are working.</p>

    <p style="margin:0 0 14px;">
        This test message was sent from the admin panel on <strong>{{ $sentAt }}</strong>
        using the SMTP details saved in Settings &rarr; Mail Settings.
    </p>

    <p style="margin:0;">No action is needed.</p>
@endsection
