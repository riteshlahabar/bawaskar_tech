{{--
    Browser-only toolbar for the admin Print view. The PDF path never passes
    $printToolbar, so dompdf never sees this and the printed page hides it.
--}}
<style>
    @media print { .doc-toolbar { display: none !important; } }
    .doc-toolbar {
        font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        max-width: 820px; margin: 14px auto 18px; text-align: right;
    }
    .doc-toolbar a, .doc-toolbar button {
        display: inline-block; background: #0da487; color: #fff; border: 0;
        padding: 8px 15px; border-radius: 6px; text-decoration: none;
        font-size: 13px; cursor: pointer; margin-left: 6px;
    }
    .doc-toolbar .ghost { background: #fff; color: #2a2d2d; border: 1px solid #cbd5e1; }
    .doc-toolbar .note { float: left; color: #6b7280; font-size: 12px; padding-top: 9px; }
</style>
<div class="doc-toolbar">
    <span class="note">Design: {{ $designLabel ?? '-' }} — change it in Settings &rarr; Company Profile</span>
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ route('admin.sales-documents.pdf', ['document' => request()->route('document'), 'id' => request()->route('id')]) }}">Download PDF</a>
    <a class="ghost" href="javascript:window.close()">Close</a>
</div>
