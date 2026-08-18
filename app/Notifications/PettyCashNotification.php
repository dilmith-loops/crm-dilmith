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
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actorName = $this->actor->name ?? 'System';
        $ref = $this->pettyCash->reference_number;
        $amountStr = "LKR " . number_format($this->pettyCash->total_amount, 2);
        $isIou = $this->pettyCash->isIOU();
        $typeStr = $isIou ? 'IOU Request' : 'Petty Cash Request';
        $url = route('petty-cash.index');

        $mail = new MailMessage();

        switch ($this->action) {
            case 'admin_approved':
                $mail->subject("Approved: {$typeStr} {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Your {$typeStr} **{$ref}** for **{$amountStr}** has been **APPROVED** by Finance / Super Admin ({$actorName}).");
                
                if ($isIou) {
                    $mail->line("⚠️ **IMPORTANT POLICY NOTICE**: This IOU must be settled with expenditure proofs and receipts **within 72 hours of approval**.");
                }
                
                $mail->action('View Request Details', $url)
                    ->line('Thank you for using Loops Finance!');
                break;

            case 'iou_settled':
                $mail->subject("IOU Request Settled: {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("The settlement for IOU request **{$ref}** ({$amountStr}) has been **APPROVED** and officially marked as **SETTLED** by {$actorName}.")
                    ->action('View Voucher & Details', route('petty-cash.voucher', $this->pettyCash->id))
                    ->line('Thank you!');
                break;

            case 'iou_reminder':
                $issuedDateStr = $this->pettyCash->issued_at ? $this->pettyCash->issued_at->format('d M Y') : 'N/A';
                $mail->subject("URGENT REMINDER: Please Settle IOU {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("This is an urgent reminder regarding your IOU request **{$ref}** for **{$amountStr}** issued on {$issuedDateStr}.")
                    ->line("⏰ **Policy Notice**: All IOUs should be settled with expenditure bills/receipts **within 72 hours of approval**.")
                    ->line("Please upload your receipts and submit your settlement request promptly.")
                    ->action('Settle IOU Now', $url)
                    ->line('Thank you for your cooperation!');
                break;

            case 'submitted':
                $mail->subject("New Request Submitted: {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("A new {$typeStr} **{$ref}** for **{$amountStr}** has been submitted by {$actorName} and requires approval.")
                    ->action('Review Request', $url);
                break;

            case 'hod_approved':
                $mail->subject("HOD Approved: {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("{$typeStr} **{$ref}** for **{$amountStr}** was approved by HOD ({$actorName}) and is awaiting Finance Approval.")
                    ->action('Review Request', $url);
                break;

            case 'hod_rejected':
            case 'admin_rejected':
                $byStr = $this->action === 'hod_rejected' ? 'HOD' : 'Finance / Super Admin';
                $mail->subject("Request Rejected: {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Your {$typeStr} **{$ref}** was **REJECTED** by {$byStr} ({$actorName}).")
                    ->line("Reason: *" . ($this->note ?: 'No reason provided') . "*")
                    ->action('View Request', $url);
                break;

            case 'reappealed':
                $mail->subject("Request Re-appealed: {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("{$typeStr} **{$ref}** has been re-appealed by {$actorName}.")
                    ->action('Review Re-appeal', $url);
                break;

            default:
                $mail->subject("Update on Petty Cash Request {$ref}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("{$typeStr} **{$ref}** was updated by {$actorName}.")
                    ->action('View Request', $url);
                break;
        }

        return $mail;
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
                $message = "Petty Cash request {$ref} was approved by HOD {$actorName} and awaits Finance approval.";
                break;
            case 'hod_rejected':
                $message = "Petty Cash request {$ref} was rejected by HOD {$actorName}. Reason: {$this->note}";
                break;
            case 'admin_approved':
                $message = "Petty Cash request {$ref} was APPROVED by {$actorName}." . ($this->pettyCash->isIOU() ? " (Must be settled within 72 hours)" : "");
                break;
            case 'admin_rejected':
                $message = "Petty Cash request {$ref} was REJECTED by {$actorName}. Reason: {$this->note}";
                break;
            case 'iou_settled':
                $message = "IOU request {$ref} settlement has been APPROVED by {$actorName}.";
                break;
            case 'iou_reminder':
                $message = "REMINDER: IOU request {$ref} requires settlement (72-hour policy).";
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
