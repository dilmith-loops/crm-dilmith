<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PettyCashVoucherMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pettyCash;
    public $action;
    public $actor;
    public $note;
    public $notifiable;

    /**
     * Create a new message instance.
     */
    public function __construct($pettyCash, $action, $actor, $note = null, $notifiable = null)
    {
        $this->pettyCash = $pettyCash;
        $this->action = $action;
        $this->actor = $actor;
        $this->note = $note;
        $this->notifiable = $notifiable;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $ref = $this->pettyCash->reference_number;
        $amountStr = "LKR " . number_format($this->pettyCash->total_amount, 2);
        $isIou = $this->pettyCash->isIOU();
        $typeStr = $isIou ? 'IOU Request' : 'Petty Cash Request';

        // Ensure relations are loaded
        $this->pettyCash->loadMissing('user', 'hod', 'items.category');

        $requesterName = $this->pettyCash->user->name ?? 'Staff member';
        $approverName = $this->actor->name ?? 'Loops Finance';

        // Determine recipient relationship safely
        $notifiableEmail = strtolower(
            is_object($this->notifiable)
                ? ($this->notifiable->email ?? (method_exists($this->notifiable, 'routeNotificationFor') ? $this->notifiable->routeNotificationFor('mail') : ($this->notifiable->routes['mail'] ?? '')))
                : ''
        );
        $notifiableId = is_object($this->notifiable) ? ((method_exists($this->notifiable, 'getKey') ? $this->notifiable->getKey() : null) ?? ($this->notifiable->id ?? null)) : null;

        $requesterEmail = strtolower($this->pettyCash->user->email ?? '');
        $hodEmail = strtolower($this->pettyCash->hod->email ?? '');

        // Requester priority: If the notifiable is the user who requested the petty cash, treat as Requester
        $isRequester = ($notifiableId && $notifiableId == $this->pettyCash->user_id) 
            || ($notifiableEmail && $requesterEmail && $notifiableEmail === $requesterEmail);

        $isHod = (!$isRequester) && (($notifiableId && $this->pettyCash->hod_id && $notifiableId == $this->pettyCash->hod_id) 
            || ($notifiableEmail && $hodEmail && $notifiableEmail === $hodEmail));

        $isSuperAdmin = (!$isRequester && !$isHod);

        $subject = match ($this->action) {
            'submitted' => $isRequester 
                ? ($isIou ? "IOU Request Received: {$ref}" : "Petty Cash Request Received: {$ref}")
                : ($isHod 
                    ? ($isIou ? "New IOU Request from {$requesterName}: {$ref}" : "New Petty Cash Request from {$requesterName}: {$ref}") 
                    : ($isIou ? "New IOU Request Submitted: {$ref}" : "New Petty Cash Request Submitted: {$ref}")),

            'hod_approved' => $isRequester
                ? ($isIou ? "IOU Request Approved by HOD: {$ref}" : "Petty Cash Request Approved by HOD: {$ref}")
                : ($isSuperAdmin 
                    ? ($isIou ? "IOU Request Waiting for Finance Approval: {$ref}" : "Petty Cash Request Waiting for Finance Approval: {$ref}") 
                    : ($isIou ? "IOU Request Approved: {$ref}" : "Petty Cash Request Approved: {$ref}")),

            'admin_approved' => $isRequester
                ? ($isIou ? "IOU Request Approved by Loops Finance: {$ref}" : "Petty Cash Request Approved by Loops Finance: {$ref}")
                : ($isHod 
                    ? ($isIou ? "Team Member IOU Request Approved: {$ref}" : "Team Member Petty Cash Request Approved: {$ref}") 
                    : ($isIou ? "IOU Request {$ref} Approved" : "Petty Cash Request {$ref} Approved")),

            'hod_rejected' => "Request Rejected by HOD: {$ref}",
            'admin_rejected' => "Request Rejected by Finance: {$ref}",
            'iou_settled' => "IOU Request Settled: {$ref}",
            'iou_reminder' => "URGENT REMINDER: Please Settle IOU {$ref}",
            'reappealed' => "Request Re-appealed: {$ref}",
            default => "Update on {$typeStr} {$ref}",
        };

        $customMessage = match ($this->action) {
            'submitted' => $isRequester
                ? ($isIou 
                    ? "Thank you, your IOU request is received and currently sent to the HOD approval."
                    : "Thank you, your petty cash request is received and currently sent to the HOD approval.")
                : ($isHod 
                    ? "Your team member {$requesterName} is requesting " . ($isIou ? "an IOU." : "a petty cash.")
                    : "A new {$typeStr} {$ref} for {$amountStr} has been submitted by {$requesterName} and sent for HOD approval."),

            'hod_approved' => $isRequester
                ? ($isIou
                    ? "Your IOU request is Approved by the HOD, and currently goes to the Finance for the Approval."
                    : "Your Petty cash request is Approved by the HOD, and currently goes to the Finance for the Approval.")
                : ($isSuperAdmin 
                    ? ($isIou ? "IOU request is waiting for the finance approval." : "Petty cash request is waiting for the finance approval.")
                    : "You have approved the {$typeStr} {$ref} for {$requesterName}. It has been sent to Finance for final approval."),

            'admin_approved' => $isRequester
                ? ($isIou
                    ? "Your IOU request was approved by the Loops Finance."
                    : "Your petty cash request was approved by the Loops Finance.")
                : ($isHod 
                    ? "Your team member {$requesterName} " . ($isIou ? "IOU request" : "petty cash request") . " was approved by the Loops Finance."
                    : "Petty cash request {$ref} is approved by {$approverName}."),

            'hod_rejected' => $isRequester
                ? "Your {$typeStr} {$ref} was REJECTED by HOD. Reason: " . ($this->note ?: 'No reason provided')
                : "Petty cash request {$ref} requested by {$requesterName} was REJECTED by HOD. Reason: " . ($this->note ?: 'No reason provided'),
            'admin_rejected' => "Your {$typeStr} {$ref} was REJECTED by Finance. Reason: " . ($this->note ?: 'No reason provided'),
            'iou_settled' => "The settlement for IOU request {$ref} ({$amountStr}) has been APPROVED and officially marked as SETTLED by Finance.",
            'iou_reminder' => "This is an urgent reminder regarding your IOU request {$ref} for {$amountStr} issued on " . ($this->pettyCash->issued_at ? $this->pettyCash->issued_at->format('d M Y') : 'N/A') . ". Please submit your expenditure proofs and settlement promptly.",
            'reappealed' => "Petty cash request {$ref} has been re-appealed by {$requesterName}.",
            default => "{$typeStr} {$ref} was updated.",
        };

        return $this->subject($subject)
            ->view('emails.petty_cash_notification')
            ->with([
                'pettyCash' => $this->pettyCash,
                'action' => $this->action,
                'actorName' => $this->actor->name ?? 'System',
                'notifiableName' => $this->notifiable->name ?? 'User',
                'customMessage' => $customMessage,
            ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        try {
            $this->pettyCash->loadMissing('user', 'hod', 'items.category');
            $ref = $this->pettyCash->reference_number;

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }

            $pdf = Pdf::loadView('emails.petty_cash_voucher_pdf', [
                'pettyCash' => $this->pettyCash,
            ])->setPaper('a4', 'portrait')
              ->setOption('isRemoteEnabled', true)
              ->setOption('isHtml5ParserEnabled', true)
              ->setOption('tempDir', $tempDir)
              ->setOption('chroot', [public_path(), base_path(), storage_path()]);

            $pdfBytes = $pdf->output();
            if (!empty($pdfBytes)) {
                $filename = "Petty_Cash_Voucher_{$ref}.pdf";
                return [
                    Attachment::fromData(fn () => $pdfBytes, $filename)
                        ->withMime('application/pdf'),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('PettyCash Mailable PDF Attachment Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }

        return [];
    }
}
