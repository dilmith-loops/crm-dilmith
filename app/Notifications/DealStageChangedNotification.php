<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealStageChangedNotification extends Notification
{
    use Queueable;

    public $deal;
    public $oldStage;
    public $newStage;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($deal, $oldStage, $newStage, $actor)
    {
        $this->deal = $deal;
        $this->oldStage = $oldStage;
        $this->newStage = $newStage;
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
            'deal_id' => $this->deal->id,
            'message' => "{$this->actor->name} changed the stage of deal '{$this->deal->title}' from '{$this->oldStage}' to '{$this->newStage}'.",
            'action_url' => route('deals.index'),
        ];
    }
}
