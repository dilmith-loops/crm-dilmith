<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerUpdateResultNotification extends Notification
{
    use Queueable;

    public $request;
    public $status;

    public function __construct($request, $status)
    {
        $this->request = $request;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $verb = ucfirst($this->status);
        $customerName = $this->request->customer->name ?? 'Customer';

        return [
            'message' => "Your update for {$customerName} was {$verb}.",
            'customer_id' => $this->request->customer_id,
            // We omit request_id so it doesn't redirect to the admin review page
        ];
    }
}
