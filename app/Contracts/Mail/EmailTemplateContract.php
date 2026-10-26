<?php

namespace App\Contracts\Mail;

use App\Support\Mail\RenderedTemplate;

/**
 * Reads the admin-edited wording for one automatic e-mail and fills in its
 * placeholders, falling back to the shipped text when the row is missing or
 * has been emptied.
 */
interface EmailTemplateContract
{
    /**
     * @param  array<string, scalar|null>  $replace  placeholder => value, keys without the leading colon
     */
    public function render(string $key, array $replace = []): RenderedTemplate;

    /** Whether this particular e-mail is switched on, master switch included. */
    public function enabled(string $key): bool;
}
