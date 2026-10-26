<?php

namespace App\Models;

use App\Services\Mail\EmailTemplateService;
use Illuminate\Database\Eloquent\Model;

/**
 * The editable wording of one automatic e-mail.
 *
 * A template owns only the words - the subject line and the covering note.
 * Everything made of data (an invoice's totals table, the bank block, the
 * order lines) stays in the Blade view, because that is the part admin must
 * not be able to break. Adding a new e-mail means adding a key to DEFAULTS,
 * a view, and whatever observer sends it; the admin page needs no change.
 */
class EmailTemplate extends Model
{
    public const INVOICE = 'invoice';

    public const WELCOME = 'welcome';

    public const ORDER_PLACED = 'order_placed';

    /**
     * Shipped wording, used to seed the table and as the fallback when a row
     * is missing or has been emptied - a blank template must never go out as
     * a blank e-mail.
     *
     * @var array<string, array{name: string, subject: string, body: string, placeholders: array<int, string>}>
     */
    public const DEFAULTS = [
        self::INVOICE => [
            'name' => 'Sale Invoice',
            'subject' => 'Invoice :invoice_no from :company',
            'body' => "Dear :name,\n\nPlease find attached your Sale Invoice :invoice_no dated :invoice_date.\n\nThank you for your business.",
            'placeholders' => [':name', ':invoice_no', ':invoice_date', ':order_no', ':amount', ':company'],
        ],
        self::WELCOME => [
            'name' => 'Welcome (new account)',
            'subject' => 'Welcome to :company',
            'body' => "Dear :name,\n\nYour :role account with :company has been created.\n\nYou can now sign in and place orders. If you did not create this account, please contact us.",
            'placeholders' => [':name', ':role', ':company'],
        ],
        self::ORDER_PLACED => [
            'name' => 'Order Placed',
            'subject' => 'We have received your order :order_no',
            'body' => "Dear :name,\n\nThank you for your order :order_no placed on :order_date.\n\nWe are reviewing it now and will let you know as soon as it is approved.",
            'placeholders' => [':name', ':order_no', ':order_date', ':amount', ':company'],
        ],
    ];

    protected $fillable = ['key', 'name', 'subject', 'body', 'is_enabled'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Edited wording must take effect on the next mail, not a minute later.
        static::saved(static fn () => EmailTemplateService::forget());
        static::deleted(static fn () => EmailTemplateService::forget());
    }

    /** The placeholders this template may use, for the admin help text. */
    public function placeholders(): string
    {
        return implode('  ', self::DEFAULTS[$this->key]['placeholders'] ?? []);
    }
}
