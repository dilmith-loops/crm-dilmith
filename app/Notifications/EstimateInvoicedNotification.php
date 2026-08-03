<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EstimateInvoicedNotification extends Notification
{
    use Queueable;

    public $estimate;
    public $invoice;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($estimate, $invoice, $actor)
    {
        $this->estimate = $estimate;
        $this->invoice = $invoice;
        $this->actor = $actor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'estimate_id' => $this->estimate->id,
            'invoice_id' => $this->invoice->id,
            'message' => "{$this->actor->name} converted estimate '{$this->estimate->reference_number}' to invoice '{$this->invoice->invoice_number}'.",
            'action_url' => route('invoices.show', $this->invoice->id),
        ];
    }
}
