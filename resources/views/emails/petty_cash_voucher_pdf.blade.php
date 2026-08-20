<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Petty Cash Voucher {{ $pettyCash->reference_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 10px;
            color: #64748b;
        }
        .voucher-title {
            font-size: 13px;
            font-weight: bold;
            color: #ec4899;
            text-align: right;
            text-transform: uppercase;
        }
        .ref-no {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
            font-family: monospace;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 10px;
            text-transform: uppercase;
        }
        .status-approved { background-color: #dcfce7; color: #166534; }
        .status-iou { background-color: #fef9c3; color: #854d0e; }
        .status-settled { background-color: #d1fae5; color: #065f46; }
        .status-default { background-color: #f1f5f9; color: #475569; }

        .policy-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            padding: 8px 12px;
            border-radius: 6px;
            color: #92400e;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .meta-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .meta-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }
        .meta-value {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 7px 10px;
            font-size: 9px;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 7px 10px;
            font-size: 10px;
        }
        .items-table tfoot td {
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
            font-weight: bold;
            font-size: 11px;
        }

        .notes-box {
            background-color: #fff7ed;
            border: 1px solid #ffedd5;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .signature-card {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .sig-image {
            max-height: 50px;
            max-width: 180px;
        }
    </style>
</head>
<body>

    @php
        $sigDataUri = null;
        if (!empty($pettyCash->signature_path)) {
            if (str_starts_with($pettyCash->signature_path, 'data:image/')) {
                $sigDataUri = $pettyCash->signature_path;
            } else {
                $cleanPath = ltrim($pettyCash->signature_path, '/');
                $possiblePaths = [
                    public_path($cleanPath),
                    base_path('public/' . $cleanPath),
                    storage_path('app/public/' . str_replace('uploads/', '', $cleanPath)),
                    storage_path('app/' . $cleanPath),
                ];
                foreach ($possiblePaths as $realSigPath) {
                    if (file_exists($realSigPath) && is_file($realSigPath)) {
                        $type = pathinfo($realSigPath, PATHINFO_EXTENSION) ?: 'png';
                        $content = @file_get_contents($realSigPath);
                        if ($content) {
                            $sigDataUri = 'data:image/' . $type . ';base64,' . base64_encode($content);
                            break;
                        }
                    }
                }
            }
        }

        $settleSigDataUri = null;
        if (!empty($pettyCash->settlement_signature_path)) {
            if (str_starts_with($pettyCash->settlement_signature_path, 'data:image/')) {
                $settleSigDataUri = $pettyCash->settlement_signature_path;
            } else {
                $cleanSettlePath = ltrim($pettyCash->settlement_signature_path, '/');
                $possibleSettlePaths = [
                    public_path($cleanSettlePath),
                    base_path('public/' . $cleanSettlePath),
                    storage_path('app/public/' . str_replace('uploads/', '', $cleanSettlePath)),
                    storage_path('app/' . $cleanSettlePath),
                ];
                foreach ($possibleSettlePaths as $realSettleSigPath) {
                    if (file_exists($realSettleSigPath) && is_file($realSettleSigPath)) {
                        $type = pathinfo($realSettleSigPath, PATHINFO_EXTENSION) ?: 'png';
                        $content = @file_get_contents($realSettleSigPath);
                        if ($content) {
                            $settleSigDataUri = 'data:image/' . $type . ';base64,' . base64_encode($content);
                            break;
                        }
                    }
                }
            }
        }
    @endphp

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-name">{{ \App\Models\Setting::get('company_name', 'LOOPS DIGITAL (PVT) LTD') }}</div>
                <div class="company-sub">{{ \App\Models\Setting::get('company_address_1', '2B, Sulaiman Terrace') }} {{ \App\Models\Setting::get('company_address_2', 'Colombo 05, Sri Lanka.') }}</div>
                <div class="company-sub">Tel: {{ \App\Models\Setting::get('company_phone', '+94 112 081 689') }} | Web: {{ \App\Models\Setting::get('company_web', 'www.loopsintegrated.com') }}</div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="voucher-title">{{ $pettyCash->isIOU() ? 'IOU PETTY CASH VOUCHER' : 'PETTY CASH VOUCHER' }}</div>
                <div class="ref-no">{{ $pettyCash->reference_number }}</div>
                <div style="margin-top: 4px;">
                    @if($pettyCash->status === 'approved')
                        <span class="status-badge status-approved">Status: Approved</span>
                    @elseif($pettyCash->status === 'iou_issued')
                        <span class="status-badge status-iou">Status: IOU Issued (Unsettled)</span>
                    @elseif($pettyCash->status === 'settled')
                        <span class="status-badge status-settled">Status: Settled</span>
                    @else
                        <span class="status-badge status-default">Status: {{ strtoupper(str_replace('_', ' ', $pettyCash->status)) }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if($pettyCash->isIOU() && $pettyCash->status !== 'settled')
    <div class="policy-box">
        ⏰ 72-Hour IOU Settlement Policy Notice: This IOU must be settled with expenditure proofs & receipts within 72 hours of approval.
    </div>
    @endif

    <!-- Metadata Grid -->
    <table class="meta-table">
        <tr>
            <td width="25%">
                <div class="meta-label">Requested By</div>
                <div class="meta-value">{{ $pettyCash->user->name ?? '-' }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">Department</div>
                <div class="meta-value">{{ $pettyCash->department ?: '-' }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">HOD Associated</div>
                <div class="meta-value">{{ $pettyCash->hod->name ?? '-' }}</div>
            </td>
            <td width="25%">
                <div class="meta-label">Job Number</div>
                <div class="meta-value">{{ $pettyCash->job_number ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 8px;">
                <div class="meta-label">Request Date</div>
                <div class="meta-value">{{ $pettyCash->created_at ? $pettyCash->created_at->format('d M Y') : '-' }}</div>
            </td>
            @if($pettyCash->issued_at || in_array($pettyCash->status, ['approved', 'iou_issued', 'settled']))
            <td style="padding-top: 8px;">
                <div class="meta-label">{{ $pettyCash->isIOU() ? 'Handover Date' : 'Approval Date' }}</div>
                <div class="meta-value">{{ $pettyCash->issued_at ? $pettyCash->issued_at->format('d M Y') : ($pettyCash->updated_at ? $pettyCash->updated_at->format('d M Y') : '-') }}</div>
            </td>
            @endif
            @if($pettyCash->isIOU())
            <td style="padding-top: 8px;" colspan="2">
                <div class="meta-label">IOU Settled Date</div>
                <div class="meta-value">{{ $pettyCash->settled_at ? $pettyCash->settled_at->format('d M Y') : ($pettyCash->status === 'pending_settlement' ? 'Pending Approval' : 'Not Settled') }}</div>
            </td>
            @endif
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="8%">#</th>
                <th width="32%">Category</th>
                <th width="35%">Description / Details</th>
                <th width="25%" style="text-align: right;">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pettyCash->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td><strong>{{ $item->category->name ?? 'General' }}</strong></td>
                <td>{{ $item->description ?: '-' }}</td>
                <td style="text-align: right; font-weight: bold;">LKR {{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Amount:</td>
                <td style="text-align: right; color: #ec4899;">LKR {{ number_format($pettyCash->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Extra Notes -->
    @if($pettyCash->extra_notes)
    <div class="notes-box">
        <strong>Extra Notes / Remarks:</strong> {{ $pettyCash->extra_notes }}
    </div>
    @endif
    @if($pettyCash->settlement_note)
    <div class="notes-box" style="background-color: #faf5ff; border-color: #f3e8ff;">
        <strong>Settlement Remarks:</strong> {{ $pettyCash->settlement_note }}
    </div>
    @endif

    <!-- Signatures -->
    <table class="signatures-table">
        <tr>
            <td width="48%" style="vertical-align: top;">
                <div class="signature-card">
                    <div style="font-size: 9px; font-weight: bold; color: #475569; text-transform: uppercase; margin-bottom: 6px;">
                        {{ $pettyCash->isIOU() ? 'IOU Issued / Handover Signature' : 'Approved Signature' }}
                    </div>
                    @if(!empty($sigDataUri))
                        <img src="{{ $sigDataUri }}" class="sig-image">
                    @else
                        <div style="height: 40px; line-height: 40px; color: #94a3b8; font-size: 10px;">Digital Signature Recorded</div>
                    @endif
                    <div style="font-size: 9px; color: #64748b; margin-top: 6px; border-top: 1px solid #e2e8f0; padding-top: 4px;">
                        Signed Person: <strong>{{ $pettyCash->user->name ?? 'Requester' }}</strong>
                    </div>
                </div>
            </td>
            @if($pettyCash->isIOU())
            <td width="4%"></td>
            <td width="48%" style="vertical-align: top;">
                <div class="signature-card" style="border-color: #a7f3d0; background-color: #ecfdf5;">
                    <div style="font-size: 9px; font-weight: bold; color: #065f46; text-transform: uppercase; margin-bottom: 6px;">
                        IOU Settlement Approval Signature
                    </div>
                    @if(!empty($settleSigDataUri))
                        <img src="{{ $settleSigDataUri }}" class="sig-image">
                    @else
                        <div style="height: 40px; line-height: 40px; color: #a7f3d0; font-size: 10px;">Pending Final Settlement</div>
                    @endif
                    <div style="font-size: 9px; color: #047857; margin-top: 6px; border-top: 1px solid #a7f3d0; padding-top: 4px;">
                        Settled By: <strong>{{ $pettyCash->user->name ?? 'Requester' }}</strong>
                    </div>
                </div>
            </td>
            @endif
        </tr>
    </table>

</body>
</html>
