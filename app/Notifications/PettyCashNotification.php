<?php

namespace App\Notifications;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
    public const ENABLE_SUPER_ADMIN_EMAILS = false;

    /**
     * Target Super Admin email addresses.
     */
    public const SUPER_ADMIN_EMAILS = [
        'rifky@loopsintegrated.com',
        'logini@loopsintegrated.com',
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

        if (!empty($notifiable->email)) {
            $isSuperAdmin = in_array(strtolower($notifiable->email), array_map('strtolower', self::SUPER_ADMIN_EMAILS));

            // If recipient is one of the Super Admins, check if Super Admin emails are enabled
            if ($isSuperAdmin) {
                if (self::ENABLE_SUPER_ADMIN_EMAILS) {
                    $channels[] = 'mail';
                }
            } else {
                // Requested Staff and Associated HOD always receive emails
                $channels[] = 'mail';
            }
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

        $subject = "";
        $customMessage = "";

        switch ($this->action) {
            case 'admin_approved':
                $subject = "Approved: {$typeStr} {$ref}";
                $customMessage = "Your {$typeStr} {$ref} for {$amountStr} has been APPROVED by Finance / Super Admin ({$actorName}).";
                break;

            case 'iou_settled':
                $subject = "IOU Request Settled: {$ref}";
                $customMessage = "The settlement for IOU request {$ref} ({$amountStr}) has been APPROVED and officially marked as SETTLED by {$actorName}.";
                break;

            case 'iou_reminder':
                $issuedDateStr = $this->pettyCash->issued_at ? $this->pettyCash->issued_at->format('d M Y') : 'N/A';
                $subject = "URGENT REMINDER: Please Settle IOU {$ref}";
                $customMessage = "This is an urgent reminder regarding your IOU request {$ref} for {$amountStr} issued on {$issuedDateStr}. Please submit your expenditure proofs and settlement promptly.";
                break;

            case 'submitted':
                $subject = "New Request Submitted: {$ref}";
                $customMessage = "A new {$typeStr} {$ref} for {$amountStr} has been submitted by {$actorName} and requires review.";
                break;

            case 'hod_approved':
                $subject = "HOD Approved: {$ref}";
                $customMessage = "{$typeStr} {$ref} for {$amountStr} was approved by HOD ({$actorName}) and is awaiting Finance Approval.";
                break;

            case 'hod_rejected':
            case 'admin_rejected':
                $byStr = $this->action === 'hod_rejected' ? 'HOD' : 'Finance / Super Admin';
                $subject = "Request Rejected: {$ref}";
                $customMessage = "Your {$typeStr} {$ref} was REJECTED by {$byStr} ({$actorName}). Reason: " . ($this->note ?: 'No reason provided');
                break;

            case 'reappealed':
                $subject = "Request Re-appealed: {$ref}";
                $customMessage = "{$typeStr} {$ref} has been re-appealed by {$actorName}.";
                break;

            default:
                $subject = "Update on Petty Cash Request {$ref}";
                $customMessage = "{$typeStr} {$ref} was updated by {$actorName}.";
                break;
        }

        // Generate PDF voucher attachment using DomPDF
        $pdfContent = null;
        try {
            $pdf = Pdf::loadView('emails.petty_cash_voucher_pdf', [
                'pettyCash' => $this->pettyCash
            ])->setPaper('a4', 'portrait');
            $pdfContent = $pdf->output();
        } catch (\Throwable $e) {
            Log::error('PettyCash PDF Generation Error: ' . $e->getMessage());
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->view('emails.petty_cash_notification', [
                'pettyCash' => $this->pettyCash,
                'action' => $this->action,
                'actorName' => $actorName,
                'notifiableName' => $notifiable->name ?? 'User',
                'customMessage' => $customMessage,
            ]);

        if ($pdfContent) {
            $filename = "Petty_Cash_Voucher_{$ref}.pdf";
            $mail->attachData($pdfContent, $filename, [
                'mime' => 'application/pdf',
            ]);
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
