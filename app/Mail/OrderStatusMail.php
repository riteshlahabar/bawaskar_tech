<?php

namespace App\Mail;

use App\Models\Sales\Order;
use App\Support\Mail\RenderedTemplate;

/**
 * Tells the buyer their order has moved to packing, dispatch, out for
 * delivery or delivered.
 *
 * Only a short reference block under the wording - the item lines went out
 * with the Order Placed mail and repeating them on every step would make four
 * long e-mails out of four short ones.
 */
class OrderStatusMail extends TemplatedMail
{
    public function __construct(RenderedTemplate $template, private readonly Order $order)
    {
        parent::__construct($template);
    }

    protected function templateView(): string
    {
        return 'emails.order-status';
    }

    protected function data(): array
    {
        return ['order' => $this->order];
    }
}
