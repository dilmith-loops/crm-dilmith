<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealUpdatedNotification extends Notification
{
    use Queueable;

    public $deal;
    public $updatedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct($deal, $updatedBy)
    {
        $this->deal = $deal;
        $this->updatedBy = $updatedBy;
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
            'deal_id' => $this->deal->id,
            'message' => "The deal '{$this->deal->title}' has been updated by {$this->updatedBy->name}.",
            'action_url' => route('deals.index'),
        ];
    }
}
