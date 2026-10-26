<?php

namespace App\Support\Mail;

/**
 * One e-mail's wording with its placeholders already filled in.
 *
 * `paragraphs()` is what the views print: the body is written in admin as
 * plain text, never HTML, so it is split on blank lines and escaped by Blade.
 * That is deliberate - a typed-in template can change the words but can never
 * break the layout or inject markup.
 */
final readonly class RenderedTemplate
{
    public function __construct(
        public string $subject,
        public string $body,
        public bool $isEnabled,
    ) {}

    /**
     * @return array<int, string>
     */
    public function paragraphs(): array
    {
        $parts = preg_split("/\R{2,}/", trim($this->body)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $p): bool => $p !== ''));
    }
}
