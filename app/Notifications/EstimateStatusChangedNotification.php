<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EstimateStatusChangedNotification extends Notification
{
    use Queueable;

    public $estimate;
    public $oldStatus;
    public $newStatus;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($estimate, $oldStatus, $newStatus, $actor)
    {
        $this->estimate = $estimate;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $old = ucfirst(str_replace('_', ' ', $this->oldStatus));
        $new = ucfirst(str_replace('_', ' ', $this->newStatus));
        
        return [
            'estimate_id' => $this->estimate->id,
            'message' => "{$this->actor->name} changed the status of estimate '{$this->estimate->reference_number}' from '{$old}' to '{$new}'.",
            'action_url' => route('estimates.show', $this->estimate->id),
        ];
    }
}
