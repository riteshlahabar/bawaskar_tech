{{--
    Browser-only chrome for the admin Print view: an A4-width sheet plus the
    action bar.

    The design templates are written for paper — they rely on `@page` margins and
    let their tables run to 100% — so in a browser they stretch across the whole
    viewport. This partial constrains the page to A4 on screen only.

    It is @includeWhen-guarded on $printToolbar, so the PDF path never renders it
    and dompdf never parses any of this. Everything here is inside @media screen
    or @media print, so printing from the browser still uses the real page box.
--}}
<style>
    /* ---------- screen: show the document as an A4 sheet ---------- */
    @media screen {
        html {
            background: #eef1f4;
            /* A4 is 210mm; keep a little breathing room around the sheet. */
            padding: 0 10px;
        }

        body {
            box-sizing: border-box;
            width: 210mm;
            max-width: 100%;
            margin: 14px auto 28px;
            padding: 10mm 10mm 12mm;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .10), 0 8px 24px rgba(16, 24, 40, .08);
        }

        /* Wide item/tax tables must scroll inside the sheet, never widen it. */
        body > table { max-width: 100%; }
    }

    /* ---------- print: get out of the way ---------- */
    @media print {
        html { background: #ffffff; padding: 0; }

        body {
            width: auto;
            max-width: none;
            margin: 0;
            padding: 0;
            background: #ffffff;
            box-shadow: none;
        }

        .doc-toolbar { display: none !important; }
    }

    /* ---------- the action bar ---------- */
    .doc-toolbar {
        font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin: 0 0 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e9ee;
    }

    .doc-toolbar .note {
        flex: 1 1 auto;
        min-width: 180px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.5;
    }

    .doc-toolbar .note strong { color: #2a2d2d; }

    .doc-toolbar a,
    .doc-toolbar button {
        flex: 0 0 auto;
        background: #0da487;
        color: #ffffff;
        border: 0;
        padding: 8px 15px;
        border-radius: 6px;
        text-decoration: none;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }

    .doc-toolbar .ghost {
        background: #ffffff;
        color: #2a2d2d;
        border: 1px solid #cbd5e1;
    }
</style>

<div class="doc-toolbar">
    <span class="note">
        Design: <strong>{{ $designLabel ?? 'Classic' }}</strong>
        &nbsp;·&nbsp; change it in Settings &rarr; Company Profile &rarr; Invoice &amp; Document Design
    </span>
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ route('admin.sales-documents.pdf', ['document' => request()->route('document'), 'id' => request()->route('id')]) }}">Download PDF</a>
    <a class="ghost" href="javascript:window.close()">Close</a>
</div>
