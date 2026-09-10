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
     * Default Super Admin fallback email addresses.
     */
    public const DEFAULT_SUPER_ADMIN_EMAILS = [];

    /**
     * Backwards-compatible alias.
     */
    public const SUPER_ADMIN_EMAILS = [];

    /**
     * Get configured Super Admin notification recipient emails from Settings.
     *
     * @return array<int, string>
     */
    public static function getConfiguredSuperAdminEmails(): array
    {
        $settingValue = \App\Models\Setting::get('super_admin_notification_emails');

        if ($settingValue === null || trim((string)$settingValue) === '') {
            return [];
        }

        $emails = preg_split('/[\r\n,;]+/', (string)$settingValue);
        $cleanEmails = [];
        foreach ($emails as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $cleanEmails[] = strtolower($email);
            }
        }

        return !empty($cleanEmails) ? array_values(array_unique($cleanEmails)) : [];
    }

    /**
     * Helper to retrieve all Super Admin notification targets (DB users matching configured emails + external mail routes).
     */
    public static function getSuperAdminRecipients($excludeUserId = null)
    {
        $recipients = collect();
        $configuredEmails = self::getConfiguredSuperAdminEmails();
        $processedEmails = [];

        foreach ($configuredEmails as $email) {
            $email = strtolower(trim($email));
            if (empty($email) || in_array($email, $processedEmails)) {
                continue;
            }
            $processedEmails[] = $email;

            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if ($user) {
                if (!$excludeUserId || $user->id != $excludeUserId) {
                    $recipients->push($user);
                }
            } else {
                $recipients->push(NotificationFacade::route('mail', $email));
            }
        }

        return $recipients;
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
