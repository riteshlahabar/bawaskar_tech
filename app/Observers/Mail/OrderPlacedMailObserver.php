<?php

namespace App\Observers\Mail;

use App\Contracts\Mail\EmailTemplateContract;
use App\Contracts\Mail\MailSenderContract;
use App\Mail\OrderPlacedMail;
use App\Models\EmailTemplate;
use App\Models\Sales\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

/**
 * Acknowledges a new order to the buyer who placed it.
 *
 * The in-app inbox row is written separately by OrderNotificationObserver;
 * this is the e-mail copy of the same moment, and it is a different observer
 * because the two have different switches and different failure rules.
 */
final class OrderPlacedMailObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly MailSenderContract $mail,
        private readonly EmailTemplateContract $templates,
    ) {}

    public function created(Order $order): void
    {
        try {
            if (! $this->templates->enabled(EmailTemplate::ORDER_PLACED)) {
                return;
            }

            $buyer = $order->order_type === 'dealer' ? $order->dealer : $order->customer;

            if ($buyer === null || blank($buyer->email)) {
                return;
            }

            $template = $this->templates->render(EmailTemplate::ORDER_PLACED, [
                'name' => $buyer->name,
                'order_no' => $order->order_no,
                'order_date' => $order->created_at?->format('d M Y') ?? '',
                'amount' => number_format((float) $order->grand_total, 2),
            ]);

            $order->loadMissing('items.product');

            $this->mail->send(new OrderPlacedMail($template, $order), $buyer->email);
        } catch (Throwable) {
            // Never let an acknowledgement stop an order from being placed.
        }
    }
}
