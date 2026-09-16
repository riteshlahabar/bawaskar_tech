<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Model;

class AppTranslation extends Model
{
    protected $fillable = ['app', 'group', 'translation_key', 'english_text', 'locale', 'value', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
