<?php

namespace App\Http\Controllers\Admin\SalesDocuments;

use App\Contracts\Sales\SalesDocumentDataContract;
use App\Contracts\Sales\SalesDocumentPdfContract;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class SalesDocumentController extends Controller
{
    public function __construct(
        private readonly SalesDocumentDataContract $documents,
        private readonly SalesDocumentPdfContract $pdf
    ) {}

    /**
     * Print shows exactly what the PDF will contain.
     *
     * It used to render its own `admin.sales-documents.invoice-{1,2,3}` views,
     * switched by a `?template=` toolbar — a second, parallel design system that
     * carried none of the GST fields (no HSN column, no CGST/SGST breakup, no
     * place of supply, no amount in words) and ignored the design chosen in
     * Company Profile. Those three views are gone; both outputs now render the
     * one design the company has selected.
     */
    public function print(string $document, int|string $id): View
    {
        $data = $this->documents->forDocument($document, $id);
        $company = $data['company'] ?? null;

        return view(
            $company instanceof CompanySetting
                ? $company->invoiceTemplateView()
                : 'invoices.pdf.document',
            [
                ...$data,
                'printToolbar' => true,
                'designLabel' => $company instanceof CompanySetting
                    ? (CompanySetting::INVOICE_TEMPLATES[$company->invoice_template] ?? 'Classic')
                    : 'Classic',
            ]
        );
    }

    public function pdf(string $document, int|string $id): Response
    {
        $data = $this->documents->forDocument($document, $id);

        return response($this->pdf->render($data), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->pdf->filename($data).'"',
        ]);
    }
}
