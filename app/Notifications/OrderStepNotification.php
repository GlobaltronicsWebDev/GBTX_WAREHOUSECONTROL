<?php

namespace App\Notifications;

use App\Models\SalesOrder;
use App\Support\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStepNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SalesOrder $salesOrder,
        public string $event, // 'advanced', 'declined', 'sent_back', 'completed', 'created'
        public ?string $actorName = null,
        public ?string $notes = null
    ) {
    }

    /**
     * Get the notification's delivery channels.
     * Sent immediately (not queued).
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->salesOrder;
        $url = route('admin.sales-orders.show', $order);
        $stepInfo = $order->step_info;

        $mail = new MailMessage();

        switch ($this->event) {
            case 'declined':
                $mail->error()
                    ->subject("[Declined] ASO {$order->aso_number} - {$order->client_name}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Sales Order **{$order->aso_number}** for **{$order->client_name}** has been **DECLINED** by {$this->actorName}.")
                    ->line("**Reason / Notes:** " . ($this->notes ?: 'No specific reason provided.'))
                    ->line("**Total Project Value:** " . $order->formatted_amount)
                    ->action('View Order Details', $url)
                    ->line('Please review the remarks and coordinate with your sales supervisor.');
                break;

            case 'completed':
                $mail->success()
                    ->subject("[Complete] ASO {$order->aso_number} - {$order->client_name} Verified & Completed")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Great news! Sales Order **{$order->aso_number}** (SJO: **{$order->sjo_number}**) has completed all workflow stages, creative checks, IT monitoring, and accounting compliance.")
                    ->line("**Client:** {$order->client_name}")
                    ->line("**BDS:** {$order->bds_name} ({$order->bds_email})")
                    ->line("**Total Project Value:** " . $order->formatted_amount)
                    ->action('View Completed Order', $url)
                    ->line('Thank you for your active collaboration in the Globaltronics ADS Sales Order System.');
                break;

            case 'sent_back':
                $mail->subject("[Revision Required] ASO {$order->aso_number} Sent Back to {$stepInfo['title']}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Sales Order **{$order->aso_number}** for **{$order->client_name}** was sent back to step: **{$stepInfo['title']}** by {$this->actorName}.")
                    ->line("**Remarks / Feedback:** " . ($this->notes ?: 'Please make required revisions.'))
                    ->action('Open Order to Review', $url)
                    ->line('Please address the notes and re-submit for review.');
                break;

            case 'created':
            case 'advanced':
            default:
                $mail->subject("[Pending Your Action] ASO {$order->aso_number} - {$stepInfo['title']}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Sales Order **{$order->aso_number}** for **{$order->client_name}** has advanced and is now waiting for your review & approval.")
                    ->line("**Current Step:** {$stepInfo['title']} ({$stepInfo['stage']})")
                    ->line("**BDS:** {$order->bds_name}")
                    ->line("**Campaign Flight:** " . ($order->start_date ? $order->start_date->format('M d, Y') : 'TBD') . ' to ' . ($order->end_date ? $order->end_date->format('M d, Y') : 'TBD'))
                    ->line("**Total Project Amount:** " . $order->formatted_amount)
                    ->when($this->notes, fn ($m) => $m->line("**Previous Sign-off Notes:** {$this->notes}"))
                    ->action('Review & Act on Order', $url)
                    ->line('Please log in to the ADS Sales Order System to review and sign off.');
                break;
        }

        return $mail;
    }
}
