<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Petty Cash Notification - {{ $pettyCash->reference_number }}</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f1f5f9; color: #334155; margin: 0; padding: 20px; line-height: 1.5;">

    <table align="center" width="100%" max-width="640" style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
        
        <!-- Header Banner with Sidebar Logo -->
        <tr>
            <td style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); padding: 24px; text-align: center; color: #ffffff;">
                @if(!empty($logoBase64))
                    <div style="margin-bottom: 12px;">
                        <img src="{{ $logoBase64 }}" alt="Loops Integrated" style="height: 44px; width: auto; display: inline-block;">
                    </div>
                @endif
                <h1 style="margin: 0; font-size: 18px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #ffffff;">
                    LOOPS FINANCE
                </h1>
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #c7d2fe; opacity: 0.9;">
                    {{ $pettyCash->isIOU() ? 'IOU Petty Cash System' : 'Petty Cash Management System' }}
                </p>
            </td>
        </tr>

        <!-- Content Body -->
        <tr>
            <td style="padding: 24px;">

                <!-- Title & Status Header -->
                <table width="100%" style="margin-bottom: 20px;">
                    <tr>
                        <td>
                            <span style="font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                                Reference Number
                            </span>
                            <h2 style="margin: 2px 0 0 0; font-size: 18px; font-weight: bold; color: #0f172a; font-family: monospace;">
                                {{ $pettyCash->reference_number }}
                            </h2>
                        </td>
                        <td style="text-align: right; vertical-align: middle;">
                            @if($action === 'admin_approved')
                                <span style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                    Approved
                                </span>
                            @elseif($action === 'iou_settled')
                                <span style="background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                    Settled
                                </span>
                            @elseif($action === 'iou_reminder')
                                <span style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                    Reminder
                                </span>
                            @else
                                <span style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                    {{ strtoupper(str_replace('_', ' ', $action)) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Main Greeting & Message -->
                <p style="font-size: 14px; color: #334155; margin-bottom: 16px;">
                    Hello <strong>{{ $notifiableName }}</strong>,
                </p>
                
                <p style="font-size: 13px; color: #475569; margin-bottom: 20px; font-weight: 500;">
                    {{ $customMessage }}
                </p>

                <!-- 72-Hour Policy Notice for IOUs -->
                @if($pettyCash->isIOU() && $pettyCash->status !== 'settled')
                <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #92400e; font-size: 12px;">
                    <strong>⏰ 72-Hour Settlement Policy Notice:</strong> All IOUs must be settled with expenditure bills and receipts <strong>within 72 hours of approval</strong>.
                </div>
                @endif

                <!-- Metadata Summary Grid Table -->
                <table width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 12px;">
                    <tr>
                        <td width="50%" style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">Requested By:</span>
                            <strong style="color: #0f172a;">{{ $pettyCash->user->name ?? '-' }}</strong>
                        </td>
                        <td width="50%" style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">Department:</span>
                            <strong style="color: #0f172a;">{{ $pettyCash->department ?: '-' }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">Associated HOD:</span>
                            <strong style="color: #0f172a;">{{ $pettyCash->hod->name ?? '-' }}</strong>
                        </td>
                        <td style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">Job Number:</span>
                            <strong style="color: #0f172a; font-family: monospace;">{{ $pettyCash->job_number ?: '-' }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">Request Date:</span>
                            <strong style="color: #0f172a;">{{ $pettyCash->created_at ? $pettyCash->created_at->format('d M Y') : '-' }}</strong>
                        </td>
                        <td style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">{{ $pettyCash->isIOU() ? 'Handover Date:' : 'Approval Date:' }}</span>
                            <strong style="color: #a855f7;">{{ $pettyCash->issued_at ? $pettyCash->issued_at->format('d M Y') : ($pettyCash->created_at ? $pettyCash->created_at->format('d M Y') : '-') }}</strong>
                        </td>
                    </tr>
                    @if($pettyCash->isIOU() && $pettyCash->settled_at)
                    <tr>
                        <td colspan="2" style="padding: 4px 0;">
                            <span style="color: #64748b; font-size: 11px; display: block;">IOU Settled Date:</span>
                            <strong style="color: #059669;">{{ $pettyCash->settled_at->format('d M Y') }}</strong>
                        </td>
                    </tr>
                    @endif
                </table>

                <!-- Line Items Table -->
                <h3 style="font-size: 13px; font-weight: bold; color: #0f172a; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                    Expenditure Details
                </h3>

                <table width="100%" style="border-collapse: collapse; margin-bottom: 20px; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #f1f5f9; text-align: left; color: #475569; font-size: 10px; text-transform: uppercase;">
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1;">Category</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1;">Description</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pettyCash->items as $item)
                        <tr>
                            <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold;">
                                {{ $item->category->name ?? 'General' }}
                            </td>
                            <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b;">
                                {{ $item->description ?: '-' }}
                            </td>
                            <td style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: bold; text-align: right; font-family: monospace;">
                                LKR {{ number_format($item->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td colspan="2" style="padding: 10px; text-align: right; border-top: 2px solid #cbd5e1; color: #0f172a;">
                                TOTAL AMOUNT:
                            </td>
                            <td style="padding: 10px; text-align: right; border-top: 2px solid #cbd5e1; color: #ec4899; font-family: monospace; font-size: 14px;">
                                LKR {{ number_format($pettyCash->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Extra Notes / Remarks -->
                @if($pettyCash->extra_notes)
                <div style="background-color: #fff7ed; border: 1px solid #ffedd5; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 12px; color: #9a3412;">
                    <strong>Extra Notes / Remarks:</strong><br>
                    <span style="color: #334155;">{{ $pettyCash->extra_notes }}</span>
                </div>
                @endif
                @if($pettyCash->settlement_note)
                <div style="background-color: #faf5ff; border: 1px solid #f3e8ff; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 12px; color: #6b21a8;">
                    <strong>Settlement Description / Remarks:</strong><br>
                    <span style="color: #334155;">{{ $pettyCash->settlement_note }}</span>
                </div>
                @endif

                <!-- CTA Button -->
                <div style="text-align: center; margin: 24px 0 16px 0;">
                    <a href="{{ route('petty-cash.index') }}" target="_blank" style="background: linear-gradient(135deg, #ec4899 0%, #a855f7 100%); color: #ffffff; text-decoration: none; padding: 12px 28px; font-size: 13px; font-weight: bold; border-radius: 8px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(168, 85, 247, 0.4);">
                        View Request Details in Portal &rarr;
                    </a>
                </div>

                <!-- Attachment Notice -->
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px 14px; text-align: center; font-size: 11px; color: #64748b;">
                    📎 <strong>Attachment Notice:</strong> The official printable PDF voucher (<code>Petty_Cash_Voucher_{{ $pettyCash->reference_number }}.pdf</code>) is attached to this email.
                </div>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8;">
                &copy; {{ date('Y') }} Loops Finance System &bull; All rights reserved.
            </td>
        </tr>
    </table>

</body>
</html>
