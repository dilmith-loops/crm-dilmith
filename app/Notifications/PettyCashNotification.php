<?php

namespace App\Notifications;

use App\Mail\PettyCashVoucherMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class PettyCashNotification extends Notification
{
    public $pettyCash;
    public $action;
    public $note;
    public $actor;

    /**
     * Target Super Admin email addresses.
     */
    public const SUPER_ADMIN_EMAILS = [
        'dilmithsenupa2@gmail.com',
        'rifky@loopsintegrated.com',
        'logini@loopsintegrated.com'
    ];

    /**
     * Helper to retrieve all Super Admin notification targets (DB users + custom emails).
     */
    public static function getSuperAdminRecipients($excludeUserId = null)
    {
        $adminsQuery = User::where(function ($q) {
            $q->where('role', 'Super Admin')->orWhere('role', 'super_admin');
        });

        if ($excludeUserId) {
            $adminsQuery->where('id', '!=', $excludeUserId);
        }

        $admins = $adminsQuery->get();

        $existingEmails = $admins->pluck('email')->map(fn($e) => strtolower($e))->toArray();

        foreach (self::SUPER_ADMIN_EMAILS as $email) {
            if (!empty($email) && !in_array(strtolower($email), $existingEmails)) {
                $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
                if ($user) {
                    if (!$excludeUserId || $user->id != $excludeUserId) {
                        $admins->push($user);
                        $existingEmails[] = strtolower($email);
                    }
                } else {
                    $admins->push(NotificationFacade::route('mail', $email));
                    $existingEmails[] = strtolower($email);
                }
            }
        }

        return $admins;
    }

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
        $channels = [];

        if (method_exists($notifiable, 'getKey') && $notifiable->getKey()) {
            $channels[] = 'database';
        }

        if (!empty($notifiable->email) || (method_exists($notifiable, 'routeNotificationFor') && $notifiable->routeNotificationFor('mail'))) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): PettyCashVoucherMail
    {
        $mailable = new PettyCashVoucherMail($this->pettyCash, $this->action, $this->actor, $this->note, $notifiable);

        $email = $notifiable->email ?? (method_exists($notifiable, 'routeNotificationFor') ? $notifiable->routeNotificationFor('mail') : null);
        if ($email) {
            $mailable->to($email);
        }

        return $mailable;
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
