{{--
    Shared shell for every e-mail this application sends.

    Deliberately plain: tables, inline styles, no Google Fonts and no remote
    images. Outlook ignores <style> blocks and most clients block external
    assets, which is exactly why the old Fastkart templates render as a wall of
    broken image icons. Keep new templates inside this shell.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? ($company?->company_name ?: config('app.name')) }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f3f6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f3f6;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #e2e6ed;border-radius:6px;font-family:Arial,Helvetica,sans-serif;color:#2b3445;">

                <tr>
                    <td style="padding:20px 24px;border-bottom:3px solid #0da487;">
                        <div style="font-size:19px;font-weight:bold;color:#0da487;">{{ $company?->company_name ?: config('app.name') }}</div>
                        @if($company?->address)
                            <div style="font-size:12px;color:#6b7280;padding-top:4px;">{{ $company->address }}</div>
                        @endif
                        @if($company?->gst_number)
                            <div style="font-size:12px;color:#6b7280;">GSTIN: {{ $company->gst_number }}</div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px;font-size:14px;line-height:1.6;">
                        @yield('content')
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px;background-color:#f7f9fc;border-top:1px solid #e2e6ed;font-size:12px;color:#6b7280;">
                        @if($company?->phone || $company?->email)
                            <div>
                                @if($company?->phone)Phone: {{ $company->phone }}@endif
                                @if($company?->phone && $company?->email) &nbsp;|&nbsp; @endif
                                @if($company?->email)Email: {{ $company->email }}@endif
                            </div>
                        @endif
                        <div style="padding-top:6px;">This is an automatically generated e-mail. Please do not reply to it.</div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
