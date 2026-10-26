<?php

namespace App\Http\Controllers\Admin\EmailTemplates;

use App\Contracts\Mail\EmailTemplateContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\CompanySetting;
use App\Models\EmailTemplate;
use Illuminate\Contracts\View\View;

/**
 * The page used to be a read-only preview of five static Fastkart HTML files
 * that no code ever sent and that carried no real data. It is now an ordinary
 * admin module over the email_templates table: the rows are fixed (a template
 * exists only if something sends it), so Add and Delete are switched off and
 * only the subject, the wording and the on/off switch can be changed.
 */
class EmailTemplateController extends AdminModuleController
{
    protected string $moduleKey = 'email-templates';

    /**
     * Shows the wording inside the real e-mail layout, with sample values in
     * place of the placeholders. Only the wording - the data block under it
     * is built from an actual invoice or order and has nothing to preview.
     */
    public function preview(EmailTemplateContract $templates, int|string $id): View
    {
        $record = EmailTemplate::query()->findOrFail($id);
        $rendered = $templates->render($record->key, self::SAMPLES);

        return view('emails.preview', [
            'paragraphs' => $rendered->paragraphs(),
            'subjectLine' => $rendered->subject,
            'company' => CompanySetting::query()->first(),
            'template' => $record,
        ]);
    }

    /**
     * Stand-in values so the preview reads like a real message.
     *
     * One list covering every placeholder in EmailTemplate::DEFAULTS, not a
     * map per template - a per-template map silently leaves a new template's
     * placeholders unfilled, which is exactly what happened when the four
     * order-status templates were added.
     */
    private const SAMPLES = [
        'name' => 'Demo Customer',
        'role' => 'Customer',
        'invoice_no' => 'INV0000001',
        'invoice_date' => '26 Oct 2026',
        'order_no' => 'CO0000001',
        'order_date' => '26 Oct 2026',
        'amount' => '1,232.00',
    ];
}
