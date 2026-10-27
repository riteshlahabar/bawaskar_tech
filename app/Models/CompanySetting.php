<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanySetting extends Model
{
    /**
     * The PDF designs a sales document can be rendered with. The key is the
     * Blade file under resources/views/invoices/pdf, so adding a design means
     * adding the view and one line here - the admin picker and the renderer
     * both read this list.
     */
    public const INVOICE_TEMPLATES = [
        'classic' => 'Classic — bordered sheet (default)',
        'teal' => 'Teal — bordered sheet in brand colour',
        'modern' => 'Modern — open layout with bank details',
    ];

    public const DEFAULT_INVOICE_TEMPLATE = 'classic';

    protected $fillable = [
        'company_name', 'logo_path', 'short_intro', 'description', 'address',
        'phone', 'whatsapp', 'email', 'website', 'map_embed_url', 'gst_number', 'cin_number',
        'founder_name', 'chairman_name', 'managing_director_name',
        'google_business_url', 'facebook_url', 'instagram_url', 'youtube_url', 'twitter_url', 'threads_url',
        'account_name', 'bank_name', 'bank_branch', 'account_number', 'ifsc_code', 'upi_id',
        'invoice_template',
    ];

    /**
     * The Blade view for the chosen design, falling back to the default when
     * the column is empty or holds a design that no longer exists.
     */
    public function invoiceTemplateView(): string
    {
        $key = (string) ($this->invoice_template ?: self::DEFAULT_INVOICE_TEMPLATE);

        if (! array_key_exists($key, self::INVOICE_TEMPLATES)) {
            $key = self::DEFAULT_INVOICE_TEMPLATE;
        }

        return $key === self::DEFAULT_INVOICE_TEMPLATE
            ? 'invoices.pdf.document'
            : 'invoices.pdf.document-'.$key;
    }

    /**
     * Whether any bank detail is filled in. The invoice footer hides the whole
     * block rather than printing a box of blanks.
     */
    public function hasBankDetails(): bool
    {
        return filled($this->bank_name)
            || filled($this->account_number)
            || filled($this->ifsc_code)
            || filled($this->upi_id);
    }

    /**
     * Google Maps embed address for the Contact page: the saved link when there
     * is one, otherwise a map of the company address. Null hides the map.
     */
    public function mapEmbedUrl(): ?string
    {
        $saved = trim((string) $this->map_embed_url);

        if ($saved !== '') {
            return $saved;
        }

        $address = trim(preg_replace('/\s+/', ' ', (string) $this->address) ?? '');

        return $address === '' ? null : 'https://www.google.com/maps?q='.rawurlencode($address).'&output=embed';
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        return Str::startsWith($this->logo_path, ['http://', 'https://'])
            ? $this->logo_path
            : asset($this->logo_path);
    }
}
