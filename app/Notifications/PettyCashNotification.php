<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PettyCashNotification extends Notification
{
    public $pettyCash;
    public $action;
    public $note;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($pettyCash, $action, $actor, $note = null)
    {
        $this->pettyCash = $pettyCash;
        $this->action = $action;
        $this->actor = $actor;
        $this->note = $note;
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
        $actorName = $this->actor->name ?? 'System';
        $ref = $this->pettyCash->reference_number;
        $message = "";

        switch ($this->action) {
            case 'submitted':
                $message = "New Petty Cash request {$ref} submitted by {$actorName} requiring HOD approval.";
                break;
            case 'hod_approved':
                $message = "Petty Cash request {$ref} was approved by HOD {$actorName} and awaits Super Admin approval.";
                break;
            case 'hod_rejected':
                $message = "Petty Cash request {$ref} was rejected by HOD {$actorName}. Reason: {$this->note}";
                break;
            case 'admin_approved':
                $message = "Petty Cash request {$ref} was APPROVED by Super Admin {$actorName}.";
                break;
            case 'admin_rejected':
                $message = "Petty Cash request {$ref} was REJECTED by Super Admin {$actorName}. Reason: {$this->note}";
                break;
            case 'reappealed':
                $message = "Petty Cash request {$ref} has been re-appealed by {$actorName}.";
                break;
            default:
                $message = "Petty Cash request {$ref} was updated by {$actorName}.";
                break;
        }

        return [
            'message' => $message,
            'petty_cash_id' => $this->pettyCash->id,
            'reference_number' => $ref,
            'actor_name' => $actorName,
            'action' => $this->action,
        ];
    }
}
