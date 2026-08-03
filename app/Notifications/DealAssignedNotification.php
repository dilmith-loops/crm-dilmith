<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealAssignedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $deal;
    public $role;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($deal, $role, $actor = null)
    {
        $this->deal = $deal;
        $this->role = $role;
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
        $actorName = $this->actor ? $this->actor->name : 'Administrator';
        return [
            'deal_id' => $this->deal->id,
            'message' => "{$actorName} added you to the deal '{$this->deal->title}' as {$this->role}.",
        ];
    }
}
