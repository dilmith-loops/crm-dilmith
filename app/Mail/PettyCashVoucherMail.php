<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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

        $subject = match ($this->action) {
            'admin_approved' => "Approved: {$typeStr} {$ref}",
            'iou_settled' => "IOU Request Settled: {$ref}",
            'iou_reminder' => "URGENT REMINDER: Please Settle IOU {$ref}",
            'submitted' => "New Request Submitted: {$ref}",
            'hod_approved' => "HOD Approved: {$ref}",
            'hod_rejected' => "Request Rejected by HOD: {$ref}",
            'admin_rejected' => "Request Rejected by Finance: {$ref}",
            'reappealed' => "Request Re-appealed: {$ref}",
            default => "Update on Petty Cash Request {$ref}",
        };

        $customMessage = match ($this->action) {
            'admin_approved' => "Your {$typeStr} {$ref} for {$amountStr} has been APPROVED by Finance.",
            'iou_settled' => "The settlement for IOU request {$ref} ({$amountStr}) has been APPROVED and officially marked as SETTLED by Finance.",
            'iou_reminder' => "This is an urgent reminder regarding your IOU request {$ref} for {$amountStr} issued on " . ($this->pettyCash->issued_at ? $this->pettyCash->issued_at->format('d M Y') : 'N/A') . ". Please submit your expenditure proofs and settlement promptly.",
            'submitted' => "A new {$typeStr} {$ref} for {$amountStr} has been submitted and requires review.",
            'hod_approved' => "{$typeStr} {$ref} for {$amountStr} was approved by HOD and is awaiting Finance Approval.",
            'hod_rejected' => "Your {$typeStr} {$ref} was REJECTED by HOD. Reason: " . ($this->note ?: 'No reason provided'),
            'admin_rejected' => "Your {$typeStr} {$ref} was REJECTED by Finance. Reason: " . ($this->note ?: 'No reason provided'),
            'reappealed' => "{$typeStr} {$ref} has been re-appealed.",
            default => "{$typeStr} {$ref} was updated.",
        };

        // Generate PDF voucher attachment using DomPDF
        $pdfBytes = null;
        $tempPdfPath = null;
        try {
            $pdf = Pdf::loadView('emails.petty_cash_voucher_pdf', [
                'pettyCash' => $this->pettyCash,
            ])->setPaper('a4', 'portrait')
              ->setOption('isRemoteEnabled', true)
              ->setOption('isHtml5ParserEnabled', true);

            $pdfBytes = $pdf->output();
            if (!empty($pdfBytes)) {
                $filename = "Petty_Cash_Voucher_{$ref}.pdf";
                $tempDir = storage_path('app/public/vouchers');
                if (!file_exists($tempDir)) {
                    @mkdir($tempDir, 0777, true);
                }
                $tempPdfPath = $tempDir . '/' . $filename;
                @file_put_contents($tempPdfPath, $pdfBytes);
            }
        } catch (\Throwable $e) {
            Log::error('PettyCash Mailable PDF Attachment Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }

        $mailable = $this->subject($subject)
            ->view('emails.petty_cash_notification')
            ->with([
                'pettyCash' => $this->pettyCash,
                'action' => $this->action,
                'actorName' => $this->actor->name ?? 'System',
                'notifiableName' => $this->notifiable->name ?? 'User',
                'customMessage' => $customMessage,
            ]);

        $filename = "Petty_Cash_Voucher_{$ref}.pdf";

        if ($tempPdfPath && file_exists($tempPdfPath)) {
            $mailable->attach($tempPdfPath, [
                'as' => $filename,
                'mime' => 'application/pdf',
            ]);
        }

        if (!empty($pdfBytes)) {
            $mailable->attachData($pdfBytes, $filename, [
                'mime' => 'application/pdf',
            ]);
        }

        return $mailable;
    }
}
