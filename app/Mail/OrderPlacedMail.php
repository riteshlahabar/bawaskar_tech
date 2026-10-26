<?php

namespace App\Mail;

use App\Models\Sales\Order;
use App\Support\Mail\RenderedTemplate;

/**
 * Acknowledges a new order to the customer or dealer who placed it.
 *
 * No PDF: the order is not a tax document and its totals can still change in
 * review, so the mail shows the lines and the figures as they stand.
 */
class OrderPlacedMail extends TemplatedMail
{
    public function __construct(RenderedTemplate $template, private readonly Order $order)
    {
        parent::__construct($template);
    }

    protected function templateView(): string
    {
        return 'emails.order-placed';
    }

    protected function data(): array
    {
        return ['order' => $this->order];
    }
}
