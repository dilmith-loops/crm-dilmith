<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceStatusPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Required: Invoice Pending Status Change')
            ->line("The invoice #{$this->invoice->invoice_number} for {$this->invoice->customer->name} has been pending for more than 30 days.")
            ->action('View Invoice', route('invoices.show', $this->invoice->id))
            ->line('Please update the status or follow up with the client.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name' => $this->invoice->customer->name,
            'amount' => $this->invoice->total_amount,
            // 'days_pending' => now()->diffInDays($this->invoice->created_at), // Handled in message or elsewhere
            'message' => "Invoice #{$this->invoice->invoice_number} pending > 30 days",
        ];
    }
}
