<?php

namespace App\Observers\Mail;

use App\Contracts\Mail\EmailTemplateContract;
use App\Contracts\Mail\MailSenderContract;
use App\Mail\OrderStatusMail;
use App\Models\EmailTemplate;
use App\Models\Sales\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

/**
 * E-mails the buyer each time their order reaches a stage they care about.
 *
 * Which stages those are is EmailTemplate::ORDER_STATUS_TEMPLATES, not a list
 * typed in here - a status with no entry simply sends nothing, exactly like
 * config/notifications.php works for the in-app inbox row.
 */
final class OrderStatusMailObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly MailSenderContract $mail,
        private readonly EmailTemplateContract $templates,
    ) {}

    public function updated(Order $order): void
    {
        try {
            if (! $order->wasChanged('status')) {
                return;
            }

            $key = EmailTemplate::ORDER_STATUS_TEMPLATES[(string) $order->status] ?? null;

            if ($key === null || ! $this->templates->enabled($key)) {
                return;
            }

            $buyer = $order->order_type === 'dealer' ? $order->dealer : $order->customer;

            if ($buyer === null || blank($buyer->email)) {
                return;
            }

            $template = $this->templates->render($key, [
                'name' => $buyer->name,
                'order_no' => $order->order_no,
                'order_date' => $order->created_at?->format('d M Y') ?? '',
                'amount' => number_format((float) $order->grand_total, 2),
                // Only the cancellation template uses this; the rest ignore it.
                'reason' => trim((string) $order->cancel_reason) ?: 'Not stated',
            ]);

            $this->mail->send(new OrderStatusMail($template, $order), $buyer->email);
        } catch (Throwable) {
            // An order must always be able to move on, mail or no mail.
        }
    }
}
