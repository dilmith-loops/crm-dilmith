@extends('layouts.app')

@section('no_sidebar')
@endsection

@push('head')
<style>
    @media print {
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-size: 12pt;
        }
        .no-print {
            display: none !important;
        }
        .print-container {
            box-shadow: none !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .page-break-before {
            page-break-before: always;
        }
        .proof-img {
            max-height: 400px !important;
            object-fit: contain !important;
        }
    }
</style>
@endpush

@php
    $getProofUrl = function($path) {
        if (!$path) return '';
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'public/')) {
            return url($clean);
        }
        return url('/public/' . $clean);
    };

    $checkFileExists = function($path) {
        if (!$path) return false;
        $clean = ltrim($path, '/');
        return file_exists(public_path($clean)) 
            || file_exists(base_path('public/' . $clean)) 
            || file_exists(base_path($clean));
    };
@endphp

@section('header')
<div class="flex justify-between items-center no-print w-full max-w-5xl mx-auto px-4 py-2">
    <div class="flex items-center space-x-2">
        <a href="{{ route('petty-cash.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-semibold transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Petty Cash
        </a>
        <span class="font-bold text-gray-800 text-sm">Petty Cash Voucher Preview</span>
    </div>
    <div class="flex items-center space-x-2">
        <button onclick="window.print()" class="bg-gradient-to-r from-brand-pink to-brand-purple text-white px-5 py-2 rounded-lg text-xs font-bold shadow-md hover:opacity-90 transition-all flex items-center">
            <i class="fas fa-print mr-2"></i> Print / Download PDF
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-2xl my-6 border border-gray-200 print-container overflow-hidden">
    <!-- Header Banner Strip -->
    <div class="h-3 bg-gradient-to-r from-brand-pink via-brand-purple to-brand-blue w-full"></div>

    <div class="p-8 space-y-6">
        <!-- Top Company & Title Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start pb-6 border-b border-gray-200 gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase">
                    {{ \App\Models\Setting::get('company_name', 'CRM SYSTEM') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    {{ \App\Models\Setting::get('company_address_1') }} {{ \App\Models\Setting::get('company_address_2') }}
                </p>
                <p class="text-xs text-gray-500">
                    Tel: {{ \App\Models\Setting::get('company_phone', '-') }} | Web: {{ \App\Models\Setting::get('company_web', '-') }}
                </p>
            </div>
            <div class="sm:text-right">
                <span class="inline-block px-3.5 py-1 text-xs font-extrabold rounded-full bg-brand-pink/10 text-brand-pink border border-brand-pink/20 uppercase tracking-wide">
                    {{ $pettyCash->isIOU() ? 'IOU PETTY CASH VOUCHER' : 'PETTY CASH VOUCHER' }}
                </span>
                <h1 class="text-xl font-mono font-bold text-gray-800 mt-2">{{ $pettyCash->reference_number }}</h1>
                <div class="mt-1">
                    @if($pettyCash->status === 'approved')
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-green-100 text-green-800">STATUS: APPROVED</span>
                    @elseif($pettyCash->status === 'iou_issued')
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-yellow-100 text-yellow-800">STATUS: IOU ISSUED (UNSETTLED)</span>
                    @elseif($pettyCash->status === 'pending_settlement')
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-purple-100 text-purple-800">STATUS: PENDING SETTLEMENT</span>
                    @elseif($pettyCash->status === 'settled')
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800">STATUS: SETTLED</span>
                    @else
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-gray-100 text-gray-800">STATUS: {{ strtoupper(str_replace('_', ' ', $pettyCash->status)) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Meta Details Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50/80 p-4 rounded-xl border border-gray-100 text-xs">
            <div>
                <span class="text-gray-500 block">Requested By:</span>
                <strong class="text-gray-900 text-sm font-bold">{{ $pettyCash->user->name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-gray-500 block">Department:</span>
                <strong class="text-gray-800 text-sm font-semibold">{{ $pettyCash->department ?: '-' }}</strong>
            </div>
            <div>
                <span class="text-gray-500 block">HOD Associated:</span>
                <strong class="text-gray-800 text-sm font-semibold">{{ $pettyCash->hod->name ?? 'Not Assigned' }}</strong>
            </div>
            <div>
                <span class="text-gray-500 block">Job Number:</span>
                <strong class="text-gray-800 text-sm font-mono font-semibold">{{ $pettyCash->job_number ?: '-' }}</strong>
            </div>

            <!-- Dates Row -->
            <div>
                <span class="text-gray-500 block">Request Date:</span>
                <strong class="text-gray-800">{{ $pettyCash->created_at ? $pettyCash->created_at->format('d M Y') : '-' }}</strong>
            </div>
            <div>
                <span class="text-gray-500 block">{{ $pettyCash->isIOU() ? 'IOU Created Date:' : 'Approval Date:' }}</span>
                <strong class="text-brand-purple font-semibold">
                    {{ $pettyCash->issued_at ? $pettyCash->issued_at->format('d M Y') : ($pettyCash->created_at ? $pettyCash->created_at->format('d M Y') : '-') }}
                </strong>
            </div>
            @if($pettyCash->isIOU())
            <div>
                <span class="text-gray-500 block">IOU Settled Date:</span>
                <strong class="text-emerald-700 font-semibold">
                    {{ $pettyCash->settled_at ? $pettyCash->settled_at->format('d M Y') : ($pettyCash->status === 'pending_settlement' ? 'Pending Approval' : 'Not Settled') }}
                </strong>
            </div>
            @endif
        </div>

        <!-- Line Items Table -->
        <div>
            <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2 flex items-center">
                <i class="fas fa-list-ul text-brand-pink mr-2"></i> Expenditure Line Items
            </h3>
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100/80 border-b border-gray-200 text-gray-700 uppercase font-semibold">
                            <th class="py-2.5 px-4">#</th>
                            <th class="py-2.5 px-4">Expense Category</th>
                            <th class="py-2.5 px-4">Description / Notes</th>
                            <th class="py-2.5 px-4 text-right">Amount (LKR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-800">
                        @forelse($pettyCash->items as $index => $item)
                            <tr>
                                <td class="py-2.5 px-4 text-gray-400 font-mono">{{ $index + 1 }}</td>
                                <td class="py-2.5 px-4 font-bold text-gray-900">{{ $item->category->name ?? 'General' }}</td>
                                <td class="py-2.5 px-4 text-gray-600">{{ $item->description ?: '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-bold font-mono">LKR {{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-400">No line items recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200 font-bold text-sm">
                            <td colspan="3" class="py-3 px-4 text-right text-gray-800 uppercase">Total Amount:</td>
                            <td class="py-3 px-4 text-right text-brand-pink font-mono">LKR {{ number_format($pettyCash->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Money Breakdown Section (Denominations) -->
        @php
            $hasIssuedNotes = is_array($pettyCash->issued_money_notes) && count(array_filter($pettyCash->issued_money_notes));
            $hasSettlementNotes = is_array($pettyCash->settlement_money_notes) && count(array_filter($pettyCash->settlement_money_notes));
        @endphp

        @if($hasIssuedNotes || $hasSettlementNotes)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($hasIssuedNotes)
                <div class="bg-gradient-to-br from-gray-50 to-blue-50/20 border border-gray-200 rounded-xl p-3.5 space-y-2">
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <h4 class="text-xs font-bold text-gray-800 flex items-center">
                            <i class="fas fa-coins text-amber-500 mr-1.5"></i> 
                            {{ $pettyCash->isIOU() ? 'Initial Cash Handed Over Notes' : 'Handed Over Money Notes' }}
                        </h4>
                        <span class="text-xs font-bold text-emerald-700">Total: LKR {{ number_format($pettyCash->issued_notes_total, 2) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                        @foreach(['5000', '2000', '1000', '500', '100', '50', '20'] as $denom)
                            @if(!empty($pettyCash->issued_money_notes[$denom]) && (int)$pettyCash->issued_money_notes[$denom] > 0)
                                <span class="px-2.5 py-1 bg-white border border-gray-200 text-gray-800 font-semibold rounded-md shadow-2xs">
                                    Rs. {{ $denom }} &times; {{ $pettyCash->issued_money_notes[$denom] }}
                                </span>
                            @endif
                        @endforeach
                        @if(!empty($pettyCash->issued_money_notes['coins']) && (float)$pettyCash->issued_money_notes['coins'] > 0)
                            <span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-900 font-semibold rounded-md shadow-2xs">
                                Coins: LKR {{ number_format((float)$pettyCash->issued_money_notes['coins'], 2) }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            @if($hasSettlementNotes)
                <div class="bg-gradient-to-br from-purple-50/40 to-pink-50/20 border border-purple-200/80 rounded-xl p-3.5 space-y-2">
                    <div class="flex justify-between items-center border-b border-purple-200 pb-2">
                        <h4 class="text-xs font-bold text-purple-900 flex items-center">
                            <i class="fas fa-coins text-brand-purple mr-1.5"></i> Settlement Money Notes
                        </h4>
                        <span class="text-xs font-bold text-purple-800">Total: LKR {{ number_format($pettyCash->settlement_notes_total, 2) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                        @foreach(['5000', '2000', '1000', '500', '100', '50', '20'] as $denom)
                            @if(!empty($pettyCash->settlement_money_notes[$denom]) && (int)$pettyCash->settlement_money_notes[$denom] > 0)
                                <span class="px-2.5 py-1 bg-white border border-purple-200 text-purple-900 font-semibold rounded-md shadow-2xs">
                                    Rs. {{ $denom }} &times; {{ $pettyCash->settlement_money_notes[$denom] }}
                                </span>
                            @endif
                        @endforeach
                        @if(!empty($pettyCash->settlement_money_notes['coins']) && (float)$pettyCash->settlement_money_notes['coins'] > 0)
                            <span class="px-2.5 py-1 bg-purple-100 border border-purple-200 text-purple-900 font-semibold rounded-md shadow-2xs">
                                Coins: LKR {{ number_format((float)$pettyCash->settlement_money_notes['coins'], 2) }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        @endif

        <!-- Remarks & Notes -->
        @if($pettyCash->settlement_note || $pettyCash->hod_rejection_note || $pettyCash->admin_rejection_note)
        <div class="space-y-2 text-xs">
            @if($pettyCash->settlement_note)
                <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-xl text-purple-950">
                    <strong class="text-brand-purple"><i class="fas fa-sticky-note mr-1"></i> Settlement Description / Remarks:</strong>
                    <p class="mt-0.5 text-gray-800">{{ $pettyCash->settlement_note }}</p>
                </div>
            @endif
            @if($pettyCash->hod_rejection_note)
                <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-900">
                    <strong>HOD Rejection Reason:</strong> {{ $pettyCash->hod_rejection_note }}
                </div>
            @endif
            @if($pettyCash->admin_rejection_note)
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-900">
                    <strong>Super Admin Rejection Reason:</strong> {{ $pettyCash->admin_rejection_note }}
                </div>
            @endif
        </div>
        @endif

        <!-- Signatures Block -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-gray-200">
            <!-- Initial Approval Signature -->
            <div class="bg-gray-50/70 border border-gray-200 rounded-xl p-4 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center mb-2">
                        <i class="fas fa-signature text-brand-purple mr-1.5"></i> Initial Cash Approval / Handover Signature
                    </h4>
                    @if($pettyCash->signature_path)
                        <div class="bg-white border border-gray-200 rounded-lg p-2 flex items-center justify-center h-24 mb-2">
                            <img src="{{ $getProofUrl($pettyCash->signature_path) }}" alt="Handover Signature" class="max-h-20 max-w-full object-contain">
                        </div>
                    @else
                        <div class="border border-dashed border-gray-300 rounded-lg h-24 flex items-center justify-center text-gray-400 text-xs mb-2 bg-white">
                            No Digital Signature Recorded
                        </div>
                    @endif
                </div>
                <div class="text-[11px] text-gray-500 pt-2 border-t border-gray-200/80">
                    <p><strong>Signed Person:</strong> {{ $pettyCash->user->name ?? 'Requester' }}</p>
                    <p><strong>Handover Date:</strong> {{ $pettyCash->issued_at ? $pettyCash->issued_at->format('d M Y') : ($pettyCash->created_at ? $pettyCash->created_at->format('d M Y') : '-') }}</p>
                </div>
            </div>

            <!-- Settlement Signature -->
            <div class="bg-emerald-50/50 border border-emerald-200/80 rounded-xl p-4 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wide flex items-center mb-2">
                        <i class="fas fa-signature text-emerald-600 mr-1.5"></i> IOU Settlement Approval Signature
                    </h4>
                    @if($pettyCash->settlement_signature_path)
                        <div class="bg-white border border-emerald-200 rounded-lg p-2 flex items-center justify-center h-24 mb-2">
                            <img src="{{ $getProofUrl($pettyCash->settlement_signature_path) }}" alt="Settlement Signature" class="max-h-20 max-w-full object-contain">
                        </div>
                    @else
                        <div class="border border-dashed border-emerald-200 rounded-lg h-24 flex items-center justify-center text-emerald-400 text-xs mb-2 bg-white">
                            {{ $pettyCash->isIOU() ? 'Pending Final Settlement Signature' : 'N/A (Standard Petty Cash)' }}
                        </div>
                    @endif
                </div>
                <div class="text-[11px] text-emerald-800 pt-2 border-t border-emerald-200/80">
                    <p><strong>Settled By:</strong> {{ $pettyCash->user->name ?? 'Requester' }}</p>
                    <p><strong>Settled Date:</strong> {{ $pettyCash->settled_at ? $pettyCash->settled_at->format('d M Y') : 'Not Settled' }}</p>
                </div>
            </div>
        </div>

        <!-- Attached Proofs Section -->
        <div class="pt-4 border-t border-gray-200">
            <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-3 flex items-center">
                <i class="fas fa-paperclip text-brand-blue mr-2"></i> Attached Proofs of Expenditure ({{ $pettyCash->proofs->count() }})
            </h3>
            
            @if($pettyCash->proofs && $pettyCash->proofs->count() > 0)
                <div class="space-y-4">
                    @php
                        $nonImages = $pettyCash->proofs->filter(function($p) {
                            $ext = strtolower(pathinfo($p->file_name, PATHINFO_EXTENSION));
                            return !in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg']);
                        });
                        $images = $pettyCash->proofs->filter(function($p) {
                            $ext = strtolower(pathinfo($p->file_name, PATHINFO_EXTENSION));
                            return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg']);
                        });
                    @endphp

                    <!-- Non-image file list -->
                    @if($nonImages->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($nonImages as $proof)
                                <a href="{{ $getProofUrl($proof->file_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-brand-blue border border-gray-200 rounded-lg text-xs font-medium transition-colors">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i> {{ $proof->file_name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- Image Receipts Embedded Grid for Print & View -->
                    @if($images->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($images as $proof)
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-center space-y-2">
                                    <div class="flex justify-between items-center text-[11px] font-semibold text-gray-600 px-1">
                                        <span class="truncate max-w-[200px]" title="{{ $proof->file_name }}">{{ $proof->file_name }}</span>
                                        <a href="{{ $getProofUrl($proof->file_path) }}" target="_blank" class="text-brand-blue hover:underline text-[10px]">
                                            <i class="fas fa-external-link-alt mr-0.5"></i> Open Full
                                        </a>
                                    </div>
                                    <a href="{{ $getProofUrl($proof->file_path) }}" target="_blank" class="block bg-white rounded-lg p-1 border border-gray-200">
                                        <img src="{{ $getProofUrl($proof->file_path) }}" alt="{{ $proof->file_name }}" class="w-full max-h-72 object-contain rounded-md proof-img mx-auto">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <p class="text-xs text-gray-400 italic">No expenditure proof documents attached to this request.</p>
            @endif
        </div>

        <!-- Footer Notice -->
        <div class="pt-6 border-t border-gray-100 flex justify-between items-center text-[11px] text-gray-400">
            <p>Generated by Loops Finance on {{ date('d M Y, h:i A') }}</p>
            <p>Page 1 of 1</p>
        </div>
    </div>
</div>
@endsection
