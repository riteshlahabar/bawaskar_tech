<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name', 'logo_path', 'short_intro', 'description', 'address',
        'phone', 'whatsapp', 'email', 'website', 'gst_number', 'cin_number',
        'founder_name', 'chairman_name', 'managing_director_name',
        'google_business_url', 'facebook_url', 'instagram_url', 'youtube_url',
    ];

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
