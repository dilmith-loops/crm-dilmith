<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerActionNotification extends Notification
{
    public $customer;
    public $action;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($customer, $action, $user)
    {
        $this->customer = $customer;
        $this->action = $action;
        $this->user = $user;
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
            'message' => "{$this->user->name} {$this->action} customer: " . ($this->customer->name ?? 'Unknown'),
            'customer_id' => $this->customer->id,
            'user_name' => $this->user->name ?? 'Unknown',
        ];
    }

}
