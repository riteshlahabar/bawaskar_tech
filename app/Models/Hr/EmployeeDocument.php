<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'salesman_id', 'document_type', 'document_no', 'file_path',
        'issued_on', 'expires_on', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return ['issued_on' => 'date', 'expires_on' => 'date'];
    }

    /**
     * Identity numbers are shown masked in the app: the salesman only needs to
     * confirm which document is on file, and a full Aadhaar or PAN on screen
     * is a needless disclosure if the handset is shoulder-surfed or screenshot.
     */
    public function getMaskedDocumentNoAttribute(): ?string
    {
        $number = $this->document_no;

        if ($number === null || $number === '') {
            return null;
        }

        $visible = substr($number, -4);

        return str_repeat('X', max(strlen($number) - 4, 0)).$visible;
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
