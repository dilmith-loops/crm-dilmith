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
        $isAnonymousAdmin = $this->notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable;
        $associatedHod = $this->pettyCash->associated_hod;
        $associatedHodId = $associatedHod ? $associatedHod->id : $this->pettyCash->hod_id;
        $associatedHodEmail = strtolower($associatedHod ? ($associatedHod->email ?? '') : ($this->pettyCash->hod->email ?? ''));

        if ($isAnonymousAdmin) {
            $isRequester = false;
            $isHod = false;
            $isSuperAdmin = true;
        } else {
            $notifiableId = is_object($this->notifiable) ? ((method_exists($this->notifiable, 'getKey') ? $this->notifiable->getKey() : null) ?? ($this->notifiable->id ?? null)) : null;

            if ($notifiableId) {
                $isRequester = ($notifiableId == $this->pettyCash->user_id);
                $isHod = (!$isRequester) && ($associatedHodId && $notifiableId == $associatedHodId);
                $isSuperAdmin = (!$isRequester && !$isHod);
            } else {
                $notifiableEmail = strtolower(
                    is_object($this->notifiable)
                        ? ($this->notifiable->email ?? (method_exists($this->notifiable, 'routeNotificationFor') ? $this->notifiable->routeNotificationFor('mail') : ($this->notifiable->routes['mail'] ?? '')))
                        : (is_string($this->notifiable) ? $this->notifiable : '')
                );
                $requesterEmail = strtolower($this->pettyCash->user->email ?? '');

                if ($notifiableEmail && $requesterEmail && $notifiableEmail === $requesterEmail) {
                    $isRequester = true;
                    $isHod = false;
                    $isSuperAdmin = false;
                } else if ($notifiableEmail && $associatedHodEmail && $notifiableEmail === $associatedHodEmail) {
                    $isRequester = false;
                    $isHod = true;
                    $isSuperAdmin = false;
                } else {
                    $isRequester = false;
                    $isHod = false;
                    $isSuperAdmin = true;
                }
            }
        }

        $subject = match ($this->action) {
            'submitted' => $isRequester 
                ? ($isIou ? "IOU Request Received: {$ref}" : "Petty Cash Request Received: {$ref}")
                : ($isHod 
                    ? ($isIou ? "New IOU Request from {$requesterName}: {$ref}" : "New Petty Cash Request from {$requesterName}: {$ref}") 
                    : ($this->pettyCash->status === 'pending_super_admin'
                        ? ($isIou ? "New IOU Request from HOD {$requesterName} (Direct to Finance): {$ref}" : "New Petty Cash Request from HOD {$requesterName} (Direct to Finance): {$ref}")
                        : ($isIou ? "New IOU Request Submitted: {$ref}" : "New Petty Cash Request Submitted: {$ref}"))),

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
            'reappealed' => ($this->pettyCash->status === 'pending_super_admin') 
                ? "Request Re-appealed by HOD: {$ref}" 
                : "Request Re-appealed: {$ref}",
            'sent_to_management' => $isRequester
                ? "Your {$typeStr} {$ref} Forwarded to Management for Approval"
                : "Approval Request: {$typeStr} {$ref} ({$amountStr}) Sent to Management",
            'management_approved' => $isSuperAdmin
                ? "Management APPROVED: {$typeStr} {$ref} ({$amountStr}) - Awaiting Finance Approval"
                : ($isRequester 
                    ? "Management Approved: Your {$typeStr} {$ref}" 
                    : "Management Approved: {$typeStr} {$ref} ({$requesterName})"),
            'management_rejected' => "Request Rejected by Management: {$ref}",
            default => "Update on {$typeStr} {$ref}",
        };

        $customMessage = match ($this->action) {
            'submitted' => $isRequester
                ? ($this->pettyCash->status === 'pending_super_admin'
                    ? ($isIou
                        ? "Thank you, your IOU request has been received and forwarded directly to Finance for approval."
                        : "Thank you, your petty cash request has been received and forwarded directly to Finance for approval.")
                    : ($isIou 
                        ? "Thank you, your IOU request is received and currently sent to the HOD approval."
                        : "Thank you, your petty cash request is received and currently sent to the HOD approval."))
                : ($isHod 
                    ? "Your team member {$requesterName} is requesting " . ($isIou ? "an IOU." : "a petty cash.")
                    : ($this->pettyCash->status === 'pending_super_admin'
                        ? "A new {$typeStr} {$ref} for {$amountStr} has been submitted by HOD {$requesterName} and forwarded directly to Finance for approval."
                        : "A new {$typeStr} {$ref} for {$amountStr} has been submitted by {$requesterName} and sent for HOD approval.")),

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
            'admin_rejected' => $isRequester
                ? ($isIou
                    ? "Your IOU request was rejected by Finance. Reason: " . ($this->note ?: 'No reason provided')
                    : "Your petty cash request was rejected by Finance. Reason: " . ($this->note ?: 'No reason provided'))
                : ($isHod 
                    ? "Your team member {$requesterName} " . ($isIou ? "IOU request" : "petty cash request") . " was rejected by Finance. Reason: " . ($this->note ?: 'No reason provided')
                    : "Petty cash request {$ref} for {$requesterName} was rejected by Finance. Reason: " . ($this->note ?: 'No reason provided')),
            'iou_settled' => "The settlement for IOU request {$ref} ({$amountStr}) has been APPROVED and officially marked as SETTLED by Finance.",
            'iou_reminder' => "This is an urgent reminder regarding your IOU request {$ref} for {$amountStr} issued on " . ($this->pettyCash->issued_at ? $this->pettyCash->issued_at->format('d M Y') : 'N/A') . ". Please submit your expenditure proofs and settlement promptly.",
            'reappealed' => "Petty cash request {$ref} has been re-appealed by {$requesterName}.",
            'sent_to_management' => $isRequester
                ? "Your {$typeStr} {$ref} for {$amountStr} has been forwarded to Management for approval by Finance."
                : "Finance Admin ({$approverName}) has submitted {$typeStr} {$ref} for {$amountStr} requested by {$requesterName} for Management approval." . ($this->note ? " Reason/Note: {$this->note}" : ""),
            'management_approved' => $isSuperAdmin
                ? "Petty cash request {$ref} for {$amountStr} requested by {$requesterName} was APPROVED by Management ({$approverName}). Please proceed with final Finance approval and cash disbursement." . ($this->note ? " Management Note: {$this->note}" : "")
                : ($isRequester
                    ? "Your {$typeStr} {$ref} for {$amountStr} was APPROVED by Management. It is now awaiting final Finance cash disbursement."
                    : "Petty cash request {$ref} for {$requesterName} was APPROVED by Management and is awaiting final Finance cash disbursement."),
            'management_rejected' => $isRequester
                ? "Your {$typeStr} {$ref} was REJECTED by Management. Reason: " . ($this->note ?: 'No reason provided')
                : "Petty cash request {$ref} for {$requesterName} was REJECTED by Management. Reason: " . ($this->note ?: 'No reason provided'),
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
                'isSuperAdmin' => $isSuperAdmin,
                'isRequester' => $isRequester,
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
