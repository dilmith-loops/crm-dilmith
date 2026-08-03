<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerUpdateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $request;

    /**
     * Create a new notification instance.
     */
    public function __construct($request)
    {
        $this->request = $request;
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
            'message' => ($this->request->user->name ?? 'A user') . ' requested a customer change: ' . ($this->request->customer->name ?? 'Unknown'),
            'customer_id' => $this->request->customer_id,
            'request_id' => $this->request->id,
            'user_name' => $this->request->user->name ?? 'Unknown',
        ];
    }
}
