<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EstimateCreatedNotification extends Notification
{
    use Queueable;

    public $estimate;
    public $deal;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($estimate, $deal, $actor)
    {
        $this->estimate = $estimate;
        $this->deal = $deal;
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
        $dealTitle = $this->deal ? " for deal '{$this->deal->title}'" : "";
        return [
            'estimate_id' => $this->estimate->id,
            'deal_id' => $this->deal ? $this->deal->id : null,
            'message' => "{$this->actor->name} created estimate '{$this->estimate->reference_number}'{$dealTitle}.",
            'action_url' => route('estimates.show', $this->estimate->id),
        ];
    }
}
