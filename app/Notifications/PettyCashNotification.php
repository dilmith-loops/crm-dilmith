<?php

namespace App\Notifications;

use App\Mail\PettyCashVoucherMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PettyCashNotification extends Notification
{
    public $pettyCash;
    public $action;
    public $note;
    public $actor;

    /**
     * Master toggle for Super Admin emails.
     * Set to true when development is finished to enable emails to Super Admins.
     */
    public const ENABLE_SUPER_ADMIN_EMAILS = true;

    /**
     * Master toggle for HOD emails.
     * Set to true to enable emails to HODs.
     */
    public const ENABLE_HOD_EMAILS = false;

    /**
     * Target Super Admin email addresses.
     */
    public const SUPER_ADMIN_EMAILS = [
        'dilmithsenupa2@gmail.com'
    ];

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
        $channels = ['database'];

        // No email notification when HOD approves (in-app notification only)
        if ($this->action === 'hod_approved') {
            return $channels;
        }

        if (!empty($notifiable->email)) {
            $isSuperAdmin = in_array(strtolower($notifiable->email), array_map('strtolower', self::SUPER_ADMIN_EMAILS));
            $isHod = ($notifiable->role === 'HOD');

            if ($isSuperAdmin) {
                if (self::ENABLE_SUPER_ADMIN_EMAILS) {
                    $channels[] = 'mail';
                }
            } elseif ($isHod) {
                if (self::ENABLE_HOD_EMAILS) {
                    $channels[] = 'mail';
                }
            } else {
                // Requested Staff always receives emails
                $channels[] = 'mail';
            }
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): PettyCashVoucherMail
    {
        return (new PettyCashVoucherMail($this->pettyCash, $this->action, $this->actor, $this->note, $notifiable))
            ->to($notifiable->email);
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
                $message = "New Petty Cash request {$ref} submitted requiring HOD approval.";
                break;
            case 'hod_approved':
                $message = "Petty Cash request {$ref} was approved by HOD and awaits Finance approval.";
                break;
            case 'hod_rejected':
                $message = "Petty Cash request {$ref} was rejected by HOD. Reason: {$this->note}";
                break;
            case 'admin_approved':
                $message = "Petty Cash request {$ref} was APPROVED by Finance." . ($this->pettyCash->isIOU() ? " (Must be settled within 72 hours)" : "");
                break;
            case 'admin_rejected':
                $message = "Petty Cash request {$ref} was REJECTED by Finance. Reason: {$this->note}";
                break;
            case 'iou_settled':
                $message = "IOU request {$ref} settlement has been APPROVED by Finance.";
                break;
            case 'iou_reminder':
                $message = "REMINDER: IOU request {$ref} requires settlement (72-hour policy).";
                break;
            case 'reappealed':
                $message = "Petty Cash request {$ref} has been re-appealed.";
                break;
            default:
                $message = "Petty Cash request {$ref} was updated.";
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
