@extends('layouts.app')

@section('header', 'Petty Cash Requests')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header & Action Bar -->
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-wallet text-brand-pink mr-3"></i> Petty Cash Requests
            </h1>
            <p class="text-sm text-gray-500 mt-1">Manage, review, and track petty cash expenditure requests across departments.</p>
        </div>
        <div class="flex items-center space-x-3 w-full md:w-auto">
            <button onclick="handleNewRequestClick(event)"
                class="w-full md:w-auto px-5 py-2.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white rounded-lg hover:opacity-90 font-medium transition-all flex items-center justify-center shadow-md">
                <i class="fas fa-plus mr-2"></i> New Petty Cash Request
            </button>
        </div>
    </div>

    @php
        $unsettledIou = \App\Models\PettyCashRequest::where('user_id', auth()->id())
            ->where('is_iou', true)
            ->whereIn('status', ['approved', 'iou_issued', 'pending_settlement'])
            ->orderBy('created_at', 'desc')
            ->first();
    @endphp

    @if($unsettledIou)
        @php
            $startDate = $unsettledIou->issued_at ?? $unsettledIou->updated_at ?? $unsettledIou->created_at;
            $deadline = $startDate ? $startDate->copy()->addHours(72) : now()->addHours(72);
            $isOverdue = now()->greaterThan($deadline);
        @endphp

        <div class="p-4 rounded-xl border {{ $isOverdue ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-amber-50 border-amber-300 text-amber-900' }} shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-lg {{ $isOverdue ? 'bg-rose-200/80 text-rose-700' : 'bg-amber-200/80 text-amber-800' }} mt-0.5 flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm flex flex-wrap items-center gap-2">
                            <span>Urgent IOU Settlement Reminder: {{ $unsettledIou->reference_number }}</span>
                            @if($isOverdue)
                                <span class="px-2 py-0.5 text-[10px] uppercase font-extrabold bg-rose-600 text-white rounded-full">OVERDUE</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] uppercase font-extrabold bg-amber-600 text-white rounded-full">72-Hour Policy</span>
                            @endif
                        </h4>
                        <p class="text-xs mt-1 leading-relaxed">
                            You have an active approved IOU for <strong>LKR {{ number_format($unsettledIou->total_amount, 2) }}</strong> approved on {{ $startDate ? $startDate->format('d M Y, h:i A') : 'N/A' }}.
                            <br>
                            @if($isOverdue)
                                <span class="font-bold text-rose-700">Settlement deadline was {{ $deadline->format('d M Y, h:i A') }} (Passed 72 hours). Please submit expenditure bills immediately!</span>
                            @else
                                <span>Settlement Deadline: <strong class="font-semibold">{{ $deadline->format('d M Y, h:i A') }}</strong>.</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex-shrink-0 w-full sm:w-auto">
                    <button type="button" onclick="openSettleIouModal({{ json_encode($unsettledIou) }})" class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold rounded-lg shadow-sm {{ $isOverdue ? 'bg-rose-600 text-white hover:bg-rose-700' : 'bg-amber-600 text-white hover:bg-amber-700' }} transition-all flex items-center justify-center gap-1.5">
                        <i class="fas fa-receipt"></i> Settle IOU Now
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Primary Scope Tabs (My Requests vs Approvals vs Team) -->
    <div class="bg-white rounded-xl shadow-sm p-2 flex border border-gray-100 space-x-2">
        <a href="{{ route('petty-cash.index', ['scope' => 'my_requests']) }}"
           class="flex-1 text-center py-2.5 px-4 rounded-lg font-bold text-sm transition-all {{ $scope === 'my_requests' ? 'bg-brand-pink text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
            <i class="fas fa-user-circle mr-1.5"></i> My Requests ({{ $myRequestsCount }})
        </a>
        @if(auth()->user()->hasRole('super_admin') || auth()->user()->role === 'Management' || auth()->user()->role === 'HOD')
            <a href="{{ route('petty-cash.index', ['scope' => 'approvals']) }}"
               class="flex-1 text-center py-2.5 px-4 rounded-lg font-bold text-sm transition-all relative {{ $scope === 'approvals' ? 'bg-brand-purple text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                <i class="fas fa-check-double mr-1.5"></i> Pending Approvals
                @if($pendingApprovalsCount > 0)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-extrabold bg-red-500 text-white">
                        {{ $pendingApprovalsCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('petty-cash.index', ['scope' => 'all_team']) }}"
               class="flex-1 text-center py-2.5 px-4 rounded-lg font-bold text-sm transition-all {{ $scope === 'all_team' ? 'bg-brand-blue text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                <i class="fas fa-users-cog mr-1.5"></i> All Team Requests
            </a>
        @endif
    </div>

    <!-- Status Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-center gap-2 border border-gray-100">
        <a href="{{ route('petty-cash.index', ['scope' => $scope]) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-full transition-all {{ !request('status') || request('status') === 'all' ? 'bg-brand-pink text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            All Statuses
        </a>
        <a href="{{ route('petty-cash.index', ['scope' => $scope, 'status' => 'pending_hod']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-full transition-all {{ request('status') === 'pending_hod' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
            Pending HOD
        </a>
        <a href="{{ route('petty-cash.index', ['scope' => $scope, 'status' => 'pending_super_admin']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-full transition-all {{ request('status') === 'pending_super_admin' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
            Pending Finance Approval
        </a>
        <a href="{{ route('petty-cash.index', ['scope' => $scope, 'status' => 'approved']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-full transition-all {{ request('status') === 'approved' ? 'bg-green-600 text-white shadow-sm' : 'bg-green-50 text-green-800 hover:bg-green-100' }}">
            Approved
        </a>
        <a href="{{ route('petty-cash.index', ['scope' => $scope, 'status' => 'rejected_by_hod']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-full transition-all {{ request('status') === 'rejected_by_hod' ? 'bg-red-600 text-white shadow-sm' : 'bg-red-50 text-red-800 hover:bg-red-100' }}">
            Rejected by HOD
        </a>
        <a href="{{ route('petty-cash.index', ['scope' => $scope, 'status' => 'rejected_by_super_admin']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-full transition-all {{ request('status') === 'rejected_by_super_admin' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
            Rejected by Finance
        </a>
    </div>

    <!-- Requests Table Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50/80 whitespace-nowrap">
                        <th class="py-4 px-6">Reference #</th>
                        <th class="py-4 px-6">Requested By</th>
                        <th class="py-4 px-6">Department</th>
                        <th class="py-4 px-6">HOD Name</th>
                        <th class="py-4 px-6">Job Number</th>
                        <th class="py-4 px-6">Total Amount</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($pettyCashes as $pc)
                        <tr class="hover:bg-gray-50/60 transition-colors whitespace-nowrap">
                            <td class="py-4 px-6 font-mono font-bold text-gray-900">{{ $pc->reference_number }}</td>
                            <td class="py-4 px-6 font-medium text-gray-800">{{ $pc->user->name ?? '-' }}</td>
                            <td class="py-4 px-6 text-gray-600">{{ $pc->department ?: '-' }}</td>
                            <td class="py-4 px-6 text-gray-600">{{ $pc->hod->name ?? 'Not Assigned' }}</td>
                            <td class="py-4 px-6 font-mono text-xs text-gray-600">{{ $pc->job_number ?: '-' }}</td>
                            <td class="py-4 px-6 font-bold text-gray-900">LKR {{ number_format($pc->total_amount, 2) }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($pc->status === 'pending_hod')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-clock mr-1"></i> Pending HOD
                                    </span>
                                @elseif($pc->status === 'pending_super_admin')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-user-shield mr-1"></i> Pending Finance Approval
                                    </span>
                                @elseif($pc->status === 'approved')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-check-circle mr-1"></i> Approved
                                    </span>
                                @elseif($pc->status === 'iou_issued')
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-300 inline-flex items-center whitespace-nowrap" title="Money Handed Over - IOU Unsettled">
                                        <i class="fas fa-hand-holding-usd mr-1"></i> Approved (IOU Unsettled)
                                    </span>
                                @elseif($pc->status === 'pending_settlement')
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-300 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Settlement Pending
                                    </span>
                                @elseif($pc->status === 'settled')
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-check-double mr-1"></i> IOU Settled
                                    </span>
                                @elseif($pc->status === 'rejected_by_hod')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 inline-flex items-center whitespace-nowrap" title="{{ $pc->hod_rejection_note }}">
                                        <i class="fas fa-times-circle mr-1"></i> Rejected by HOD
                                    </span>
                                @elseif($pc->status === 'rejected_by_super_admin')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 inline-flex items-center whitespace-nowrap" title="{{ $pc->admin_rejection_note }}">
                                        <i class="fas fa-ban mr-1"></i> Rejected by Finance
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <button onclick="viewPettyCashDetails({{ $pc->id }})"
                                        class="px-2.5 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </button>
                                    <a href="{{ route('petty-cash.download', $pc) }}?with_buttons=1" target="_blank"
                                        class="px-2.5 py-1.5 bg-brand-blue text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center whitespace-nowrap shadow-sm" title="Download / Print Voucher">
                                        <i class="fas fa-file-pdf mr-1"></i> Voucher
                                    </a>

                                    <!-- Settle IOU Button for Requester or Admin -->
                                    @if($pc->status === 'iou_issued' && (auth()->id() === $pc->user_id || auth()->user()->hasRole('super_admin')))
                                        <button onclick="openSettleIouModal({{ $pc->id }})"
                                            class="px-2.5 py-1.5 bg-brand-purple text-white text-xs font-semibold rounded-lg hover:bg-brand-pink transition-colors inline-flex items-center whitespace-nowrap shadow-sm">
                                            <i class="fas fa-file-signature mr-1"></i> Settle IOU
                                        </button>
                                    @endif

                                    <!-- HOD Actions -->
                                    @if($pc->status === 'pending_hod' && (auth()->user()->id === $pc->hod_id || auth()->user()->role === 'HOD' || auth()->user()->hasRole('super_admin')))
                                        <form action="{{ route('petty-cash.hodApprove', $pc) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition-colors inline-flex items-center whitespace-nowrap">
                                                <i class="fas fa-check mr-1"></i> Accept
                                            </button>
                                        </form>
                                        <button onclick="openHodRejectModal({{ $pc->id }})"
                                            class="px-2.5 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors inline-flex items-center whitespace-nowrap">
                                            <i class="fas fa-times mr-1"></i> Reject
                                        </button>
                                    @endif

                                    <!-- Super Admin Actions (Allowed Anytime) -->
                                    @if(auth()->user()->hasRole('super_admin') || auth()->user()->role === 'Management')
                                        @if(!in_array($pc->status, ['approved', 'settled']))
                                            <button onclick="openAdminApproveModal({{ $pc->id }}, '{{ $pc->reference_number }}', '{{ addslashes($pc->user->name ?? 'Staff') }}', {{ $pc->isIOU() ? 'true' : 'false' }}, '{{ $pc->status }}', {{ $pc->total_amount }})"
                                                class="px-2.5 py-1.5 bg-brand-pink text-white text-xs font-semibold rounded-lg hover:bg-brand-purple transition-colors inline-flex items-center whitespace-nowrap">
                                                <i class="fas fa-check-double mr-1"></i> {{ $pc->status === 'pending_settlement' ? 'Approve Settlement' : 'Approve' }}
                                            </button>
                                        @endif
                                        @if($pc->status !== 'rejected_by_super_admin' && $pc->status !== 'settled')
                                            <button onclick="openAdminRejectModal({{ $pc->id }})"
                                                class="px-2.5 py-1.5 bg-rose-700 text-white text-xs font-semibold rounded-lg hover:bg-rose-800 transition-colors inline-flex items-center whitespace-nowrap">
                                                <i class="fas fa-ban mr-1"></i> Reject
                                            </button>
                                        @endif
                                    @endif

                                    <!-- Super Admin Edit & Delete Options -->
                                    @if(auth()->user()->hasRole('super_admin'))
                                        <button onclick="openEditPettyCashModal({{ $pc->id }})"
                                            class="px-2.5 py-1.5 bg-amber-600 text-white text-xs font-semibold rounded-lg hover:bg-amber-700 transition-colors inline-flex items-center whitespace-nowrap shadow-sm" title="Edit Request">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <form action="{{ route('petty-cash.destroy', $pc) }}" method="POST" class="inline-block" onsubmit="return confirmDeletePettyCash(event, this, '{{ $pc->reference_number }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1.5 bg-red-700 text-white text-xs font-semibold rounded-lg hover:bg-red-800 transition-colors inline-flex items-center whitespace-nowrap shadow-sm" title="Delete Request">
                                                <i class="fas fa-trash-alt mr-1"></i> Delete
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Re-appeal Action -->
                                    @if(in_array($pc->status, ['rejected_by_hod', 'rejected_by_super_admin']) && (auth()->id() === $pc->user_id || auth()->id() === $pc->hod_id || auth()->user()->hasRole('super_admin')))
                                        <button onclick="openReappealModal({{ $pc->id }})"
                                            class="px-2.5 py-1.5 bg-brand-blue text-white text-xs font-semibold rounded-lg hover:bg-brand-purple transition-colors inline-flex items-center whitespace-nowrap">
                                            <i class="fas fa-redo mr-1"></i> Re-appeal
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <i class="fas fa-receipt text-4xl mb-3 block"></i>
                                No Petty Cash requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Petty Cash Request Modal (Available to All Users) -->
<div id="newPettyCashModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-6 border w-11/12 max-w-3xl shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-wallet text-brand-pink mr-2"></i> New Petty Cash Request
            </h3>
            <button onclick="document.getElementById('newPettyCashModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('petty-cash.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HOD Associated With *</label>
                    <select name="hod_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-blue focus:ring-brand-blue">
                        @foreach($hods as $h)
                            <option value="{{ $h->id }}" {{ auth()->user()->supervisor_id == $h->id ? 'selected' : '' }}>
                                {{ $h->name }} ({{ $h->department ?: 'HOD' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                    <select name="job_number" id="create_job_number" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-blue focus:ring-brand-blue">
                        <option value="">-- Select Job Number (Optional) --</option>
                        @foreach($jobs as $jobNo => $display)
                            <option value="{{ $jobNo }}">{{ $display }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-gray-800">Expense Line Items *</label>
                    <button type="button" onclick="addExpenseItemRow()" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple transition-all">
                        <i class="fas fa-plus mr-1"></i> Add Line Item
                    </button>
                </div>
                <div id="expenseItemsContainer" class="space-y-3">
                    <!-- Initial Row -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="md:col-span-4">
                            <select name="items[0][expense_category_id]" required onchange="toggleDinnerAttendees(this)" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
                                <option value="">Select Category *</option>
                                @foreach($expenseCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <input type="number" step="0.01" min="0.01" name="items[0][amount]" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
                        </div>
                        <div class="md:col-span-4">
                            <input type="text" name="items[0][description]" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
                        </div>
                        <div class="md:col-span-1 text-right">
                            <button type="button" onclick="if(document.querySelectorAll('#expenseItemsContainer .grid').length > 1) this.closest('.grid').remove()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="attendees-container hidden col-span-1 md:col-span-12 mt-2 p-2.5 bg-blue-50/80 border border-blue-200 rounded-lg text-xs">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-blue-900 flex items-center gap-1.5">
                                    <i class="fas fa-utensils text-brand-blue"></i> Dinner Attendees / People Names (Max 5):
                                </span>
                                <span class="text-[11px] text-blue-700">Enter names of people who attended</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                                <input type="text" name="items[0][attendees][]" placeholder="Person 1 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                                <input type="text" name="items[0][attendees][]" placeholder="Person 2 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                                <input type="text" name="items[0][attendees][]" placeholder="Person 3 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                                <input type="text" name="items[0][attendees][]" placeholder="Person 4 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                                <input type="text" name="items[0][attendees][]" placeholder="Person 5 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">Extra Notes / Remarks</label>
                <textarea name="extra_notes" rows="2" placeholder="Optional extra notes, remarks, or justification for this request..." class="w-full rounded-lg border-gray-300 text-xs focus:border-brand-blue focus:ring-brand-blue"></textarea>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-gray-800">Proofs of Expenditure</label>
                    <button type="button" onclick="addProofFileInput('newProofContainer')" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple transition-all flex items-center">
                        <i class="fas fa-plus mr-1"></i> Add File
                    </button>
                </div>
                <div id="newProofContainer" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-brand-blue hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1">
                        <button type="button" onclick="if(document.querySelectorAll('#newProofContainer > div').length > 1) this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-1 flex-shrink-0">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">Upload receipts, bills, or invoices (PNG, JPG, PDF, DOCX). Click "Add File" to select multiple files.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('newPettyCashModal').classList.add('hidden')"
                    class="px-5 py-2.5 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white font-medium rounded-lg hover:opacity-90 shadow-md">
                    Submit to HOD
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-6 border w-11/12 max-w-3xl shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 flex items-center" id="modalRef">
                <i class="fas fa-info-circle text-brand-blue mr-2"></i> Request Details
            </h3>
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mt-4 space-y-6" id="modalBody">
            <!-- Dynamic Data inserted by JS -->
        </div>
        <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-6" id="modalFooter">
            <a id="modalVoucherLink" href="#" target="_blank"
                class="px-4 py-2 bg-gradient-to-r from-brand-pink to-brand-purple text-white text-xs font-bold rounded-lg hover:opacity-90 transition-colors inline-flex items-center shadow-md">
                <i class="fas fa-file-pdf mr-1.5"></i> Download Voucher (PDF)
            </a>
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')"
                class="px-5 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<!-- HOD Reject Modal -->
<div id="hodRejectModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 text-red-600">
                <i class="fas fa-times-circle mr-2"></i> Reject Petty Cash Request (HOD)
            </h3>
            <button onclick="document.getElementById('hodRejectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="hodRejectForm" action="" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason / Note for Rejection *</label>
                <textarea name="hod_rejection_note" required rows="4" placeholder="Please state the exact reason for rejecting this request..." class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('hodRejectModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Admin Approve & Signature Modal -->
<div id="adminApproveModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-5 sm:p-6 border w-full max-w-xl shadow-2xl rounded-2xl bg-white max-h-[92vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center" id="adminApproveModalRef">
                <i class="fas fa-check-double text-brand-pink mr-2"></i> Approve Petty Cash Request
            </h3>
            <button onclick="closeAdminApproveModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="adminApproveForm" action="" method="POST" onsubmit="prepareSignatureSubmission(event)" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="signature" id="signatureInput">

            <div id="iouMandatoryBanner" class="bg-amber-50 border-l-4 border-amber-500 rounded-xl p-3 text-xs text-amber-900 hidden">
                <p class="font-bold flex items-center"><i class="fas fa-exclamation-triangle text-amber-600 mr-1.5"></i> Mandatory Signature Required for IOU</p>
                <p class="text-amber-800 text-[11px] mt-0.5">Since this request contains an IOU expense category, a drawn signature from the requested person is REQUIRED to proceed with approval or settlement.</p>
            </div>

            <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3.5 text-xs text-blue-900 space-y-1.5">
                <div class="flex flex-wrap justify-between items-center gap-2 pb-1 border-b border-blue-100/80">
                    <p><strong>Requester:</strong> <span id="approveRequesterName">-</span></p>
                    <p><strong>Requested Amount:</strong> <span id="approveRequestedAmount" class="font-bold text-brand-purple">LKR 0.00</span></p>
                </div>
                <p class="text-blue-700" id="approveConfirmationText">Are you sure you want to approve this petty cash request?</p>
            </div>

            <!-- Approval / Handed Over Date Input -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1" id="approveDateLabel">
                    <i class="fas fa-calendar-alt text-brand-blue mr-1"></i> Approval Date *
                </label>
                <input type="date" name="issued_at" id="approveDateInput" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 text-xs focus:border-brand-purple focus:ring-brand-purple">
                <p class="text-[11px] text-gray-400 mt-1">By default, set to current sign date. You can modify if needed.</p>
            </div>

            <!-- Money Notes Breakdown (Optional) -->
            <div class="bg-gradient-to-r from-gray-50 to-blue-50/30 border border-gray-200 rounded-xl p-3.5 space-y-2.5">
                <div class="flex justify-between items-center cursor-pointer" onclick="document.getElementById('approveMoneyBreakdownBody').classList.toggle('hidden')">
                    <label class="text-xs font-bold text-gray-800 flex items-center cursor-pointer">
                        <i class="fas fa-money-bill-wave text-emerald-600 mr-1.5"></i> 
                        <span id="approveMoneyNotesSectionTitle">Money Notes Breakdown (Handed Over)</span>
                        <span class="text-gray-400 font-normal ml-1 text-[11px]">(Optional)</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" id="approveMoneyNotesTotalDisplay">Total: LKR 0.00</span>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </div>
                </div>
                <div id="approveMoneyBreakdownBody" class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-gray-200/60">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 5000</label>
                        <input type="number" min="0" name="issued_money_notes[5000]" id="an_5000" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="5000" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 2000</label>
                        <input type="number" min="0" name="issued_money_notes[2000]" id="an_2000" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="2000" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 1000</label>
                        <input type="number" min="0" name="issued_money_notes[1000]" id="an_1000" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="1000" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 500</label>
                        <input type="number" min="0" name="issued_money_notes[500]" id="an_500" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="500" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 100</label>
                        <input type="number" min="0" name="issued_money_notes[100]" id="an_100" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="100" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 50</label>
                        <input type="number" min="0" name="issued_money_notes[50]" id="an_50" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="50" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 20</label>
                        <input type="number" min="0" name="issued_money_notes[20]" id="an_20" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-note-input" data-value="20" oninput="calculateApproveNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Coins (LKR)</label>
                        <input type="number" step="0.01" min="0" name="issued_money_notes[coins]" id="an_coins" placeholder="0.00" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple approve-coin-input" oninput="calculateApproveNotesTotal()">
                    </div>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-bold text-gray-700">
                        <i class="fas fa-signature text-brand-purple mr-1"></i> Requested Person Signature 
                        <span id="signatureRequiredText" class="text-red-600 font-bold hidden">(MANDATORY FOR IOU)</span>
                        <span id="signatureOptionalText" class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <button type="button" onclick="clearSignatureCanvas()" class="text-[11px] text-red-500 hover:text-red-700 font-semibold flex items-center">
                        <i class="fas fa-eraser mr-1"></i> Clear Signature
                    </button>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-1 bg-gray-50 touch-none">
                    <canvas id="signatureCanvas" width="450" height="150" class="w-full h-36 bg-white rounded-lg cursor-crosshair border border-gray-200"></canvas>
                </div>
                <p class="text-[11px] text-gray-400 mt-1" id="signatureHintText">Sign on the canvas above using mouse or touch.</p>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAdminApproveModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-gradient-to-r from-brand-pink to-brand-purple text-white text-xs font-bold rounded-lg hover:opacity-90 shadow-md flex items-center">
                    <i class="fas fa-check mr-1.5"></i> Confirm Approval
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Settle IOU Modal -->
<div id="settleIouModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-5 sm:p-6 border w-full max-w-2xl shadow-2xl rounded-2xl bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center" id="settleIouModalRef">
                <i class="fas fa-file-signature text-brand-purple mr-2"></i> Settle IOU Request
            </h3>
            <button onclick="document.getElementById('settleIouModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="settleIouForm" action="" method="POST" enctype="multipart/form-data" class="mt-4 space-y-5">
            @csrf
            <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-900 flex items-start gap-2.5">
                <i class="fas fa-clock text-brand-purple text-lg mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong class="text-purple-950 font-extrabold block mb-0.5">IOU 72-Hour Settlement Requirement:</strong>
                    All IOUs must be settled with expenditure proofs (receipts, bills, or invoices) <strong>within 72 hours of approval</strong>. Once submitted, Finance will review and approve the settlement.
                </div>
            </div>

            <!-- IOU Settled Date & Settlement Remarks -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        <i class="fas fa-calendar-check text-brand-purple mr-1"></i> IOU Settled Date *
                    </label>
                    <input type="date" name="settled_at" id="settleDateInput" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 text-xs focus:border-brand-purple focus:ring-brand-purple">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        <i class="fas fa-comment-alt text-brand-purple mr-1"></i> Extra Notes / Remarks
                    </label>
                    <textarea name="extra_notes" id="settleNoteInput" rows="2" placeholder="Optional extra notes or details about settlement..." class="w-full rounded-lg border-gray-300 text-xs focus:border-brand-purple focus:ring-brand-purple"></textarea>
                    <input type="hidden" name="settlement_note" id="settleNoteHiddenInput">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-800 mb-2">Final Expenditure Line Items & Amounts</label>
                <div id="settleItemsContainer" class="space-y-3">
                    <!-- Dynamic JS content -->
                </div>
            </div>

            @if(auth()->user()->hasRole('super_admin'))
            <!-- Money Notes Breakdown for Settlement (Optional - Super Admin Only) -->
            <div class="bg-gradient-to-r from-purple-50/50 to-pink-50/30 border border-purple-100 rounded-xl p-3.5 space-y-2.5">
                <div class="flex justify-between items-center cursor-pointer" onclick="document.getElementById('settleMoneyBreakdownBody').classList.toggle('hidden')">
                    <label class="text-xs font-bold text-purple-900 flex items-center cursor-pointer">
                        <i class="fas fa-coins text-brand-purple mr-1.5"></i> Settlement Money Notes <span class="text-gray-400 font-normal ml-1 text-[11px]">(Optional)</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded border border-purple-200" id="settleMoneyNotesTotalDisplay">Total: LKR 0.00</span>
                        <i class="fas fa-chevron-down text-xs text-purple-400"></i>
                    </div>
                </div>
                <div id="settleMoneyBreakdownBody" class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-purple-100">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 5000</label>
                        <input type="number" min="0" name="settlement_money_notes[5000]" id="sn_5000" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="5000" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 2000</label>
                        <input type="number" min="0" name="settlement_money_notes[2000]" id="sn_2000" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="2000" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 1000</label>
                        <input type="number" min="0" name="settlement_money_notes[1000]" id="sn_1000" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="1000" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 500</label>
                        <input type="number" min="0" name="settlement_money_notes[500]" id="sn_500" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="500" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 100</label>
                        <input type="number" min="0" name="settlement_money_notes[100]" id="sn_100" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="100" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 50</label>
                        <input type="number" min="0" name="settlement_money_notes[50]" id="sn_50" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="50" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Rs. 20</label>
                        <input type="number" min="0" name="settlement_money_notes[20]" id="sn_20" placeholder="0" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-note-input" data-value="20" oninput="calculateSettleNotesTotal()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600">Coins (LKR)</label>
                        <input type="number" step="0.01" min="0" name="settlement_money_notes[coins]" id="sn_coins" placeholder="0.00" class="w-full rounded-md border-gray-300 text-xs text-right py-1 px-2 focus:ring-brand-purple settle-coin-input" oninput="calculateSettleNotesTotal()">
                    </div>
                </div>
            </div>
            @endif

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-bold text-gray-800">Proofs of Expenditure * (Receipts / Bills)</label>
                    <button type="button" onclick="addProofFileInput('settleProofsContainer')" class="text-[11px] text-brand-purple hover:underline font-semibold flex items-center">
                        <i class="fas fa-plus mr-1"></i> Add Another File
                    </button>
                </div>
                <div id="settleProofsContainer" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="file" name="proofs[]" required accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-brand-purple hover:file:bg-purple-100 border border-gray-200 rounded-lg p-1">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('settleIouModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-gradient-to-r from-brand-purple to-brand-pink text-white text-xs font-bold rounded-lg hover:opacity-90 shadow-md flex items-center">
                    <i class="fas fa-paper-plane mr-1.5"></i> Submit Settlement Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Admin Reject Modal -->
<div id="adminRejectModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-bold text-rose-600">
                <i class="fas fa-ban mr-2"></i> Reject Petty Cash Request (Finance)
            </h3>
            <button onclick="document.getElementById('adminRejectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="adminRejectForm" action="" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Finance Rejection Reason *</label>
                <textarea name="admin_rejection_note" required rows="4" placeholder="State reason for rejection (Will notify both Staff & HOD)..." class="w-full rounded-lg border-gray-300 focus:border-rose-500 focus:ring-rose-500 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('adminRejectModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-rose-700 text-white text-sm font-medium rounded-lg hover:bg-rose-800">
                    Reject & Notify Both
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Re-appeal Modal -->
<div id="reappealModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-6 border w-11/12 max-w-3xl shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-redo text-brand-blue mr-2"></i> Re-appeal Petty Cash Request
            </h3>
            <button onclick="document.getElementById('reappealModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reappealForm" action="" method="POST" enctype="multipart/form-data" class="mt-4 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HOD Associated With *</label>
                    <select name="hod_id" id="reappeal_hod_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-blue focus:ring-brand-blue">
                        @foreach($hods as $h)
                            <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->department ?: 'HOD' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                    <select name="job_number" id="reappeal_job_number" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-blue focus:ring-brand-blue">
                        <option value="">-- Select Job Number (Optional) --</option>
                        @foreach($jobs as $jobNo => $display)
                            <option value="{{ $jobNo }}">{{ $display }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-gray-800">Expense Line Items *</label>
                    <button type="button" onclick="addReappealItemRow()" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple">
                        <i class="fas fa-plus mr-1"></i> Add Line
                    </button>
                </div>
                <div id="reappealItemsContainer" class="space-y-3">
                    <!-- Dynamic Rows -->
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">Extra Notes / Remarks</label>
                <textarea name="extra_notes" id="reappealExtraNotes" rows="2" placeholder="Optional extra notes or justification for re-appeal..." class="w-full rounded-lg border-gray-300 text-xs focus:border-brand-blue focus:ring-brand-blue"></textarea>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-gray-800">Add Additional Expenditure Proofs</label>
                    <button type="button" onclick="addProofFileInput('reappealProofContainer')" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple transition-all flex items-center">
                        <i class="fas fa-plus mr-1"></i> Add File
                    </button>
                </div>
                <div id="reappealProofContainer" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-brand-blue hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1">
                        <button type="button" onclick="if(document.querySelectorAll('#reappealProofContainer > div').length > 1) this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-1 flex-shrink-0">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('reappealModal').classList.add('hidden')"
                    class="px-5 py-2.5 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white font-medium rounded-lg hover:opacity-90 shadow-md">
                    Resubmit Re-appeal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Petty Cash Request Modal (Super Admin Only) -->
<div id="editPettyCashModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-5 sm:p-6 border w-full max-w-3xl shadow-2xl rounded-2xl bg-white max-h-[92vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center" id="editModalRef">
                <i class="fas fa-edit text-amber-600 mr-2"></i> Edit Petty Cash Request
            </h3>
            <button onclick="document.getElementById('editPettyCashModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="editPettyCashForm" action="" method="POST" enctype="multipart/form-data" class="mt-4 space-y-5">
            @csrf
            @method('PUT')

            <!-- Requester Banner -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900 flex justify-between items-center">
                <p><strong>Requester:</strong> <span id="editRequesterName">-</span></p>
                <p><strong>Reference:</strong> <span id="editRefDisplay" class="font-mono font-bold">-</span></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">HOD Associated With *</label>
                    <select name="hod_id" id="editHodId" required class="w-full rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500">
                        @foreach($hods as $h)
                            <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->department ?: 'HOD' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Job Number</label>
                    <select name="job_number" id="editJobNumber" class="w-full rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500">
                        <option value="">-- Select Job Number (Optional) --</option>
                        @foreach($jobs as $jobNo => $display)
                            <option value="{{ $jobNo }}">{{ $display }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status *</label>
                    <select name="status" id="editStatus" required class="w-full rounded-lg border-gray-300 text-xs font-semibold focus:border-amber-500 focus:ring-amber-500" onchange="toggleEditDateFields()">
                        <option value="pending_hod">Pending HOD</option>
                        <option value="pending_super_admin">Pending Finance Approval</option>
                        <option value="approved">Approved</option>
                        <option value="iou_issued">Approved (IOU Unsettled)</option>
                        <option value="pending_settlement">Settlement Pending</option>
                        <option value="settled">IOU Settled</option>
                        <option value="rejected_by_hod">Rejected by HOD</option>
                        <option value="rejected_by_super_admin">Rejected by Finance</option>
                    </select>
                </div>
            </div>

            <!-- Date Fields -->
            <div class="flex flex-wrap items-start gap-4 sm:gap-6" id="editDateFieldsGrid">
                <div id="editCreatedAtGroup" class="w-full sm:w-auto">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        <i class="fas fa-calendar-alt text-brand-blue mr-1"></i> Request Date (Created) *
                    </label>
                    <input type="date" name="created_at" id="editCreatedAt" required class="w-full sm:w-48 rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500 shadow-sm">
                </div>
                <div id="editIssuedAtGroup" class="w-full sm:w-auto">
                    <label class="block text-xs font-bold text-gray-700 mb-1" id="editIssuedAtLabel">
                        <i class="fas fa-calendar-check text-brand-purple mr-1"></i> Approval Date
                    </label>
                    <input type="date" name="issued_at" id="editIssuedAt" class="w-full sm:w-48 rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500 shadow-sm">
                </div>
                <div id="editSettledAtGroup" class="w-full sm:w-auto hidden">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        <i class="fas fa-calendar-day text-emerald-600 mr-1"></i> IOU Settled Date
                    </label>
                    <input type="date" name="settled_at" id="editSettledAt" class="w-full sm:w-48 rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500 shadow-sm">
                </div>
            </div>

            <!-- Line Items Section -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-gray-800">Expense Line Items *</label>
                    <button type="button" onclick="addEditExpenseItemRow()" class="text-xs bg-amber-600 text-white px-3 py-1 rounded-md hover:bg-amber-700 transition-all font-semibold">
                        <i class="fas fa-plus mr-1"></i> Add Line Item
                    </button>
                </div>
                <div id="editExpenseItemsContainer" class="space-y-2">
                    <!-- Dynamic rows inserted here -->
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">Extra Notes / Remarks</label>
                <textarea name="extra_notes" id="editExtraNotes" rows="2" placeholder="Optional extra notes, remarks, or justification..." class="w-full rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500"></textarea>
            </div>

            <!-- Proof Attachments Section -->
            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">Existing Proofs / Attachments</label>
                <div id="editExistingProofsContainer" class="space-y-1.5 mb-3 bg-gray-50 p-2.5 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-400 italic">No existing proofs attached.</p>
                </div>

                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-bold text-gray-800">Upload Additional Proofs</label>
                    <button type="button" onclick="addProofFileInput('editProofContainer')" class="text-xs bg-gray-700 text-white px-2.5 py-1 rounded-md hover:bg-gray-800 transition-all">
                        <i class="fas fa-plus mr-1"></i> Add File
                    </button>
                </div>
                <div id="editProofContainer" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 border border-gray-200 rounded-lg p-1">
                        <button type="button" onclick="if(document.querySelectorAll('#editProofContainer > div').length > 1) this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-xs px-1 flex-shrink-0">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                <button type="button" onclick="document.getElementById('editPettyCashModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-medium rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 shadow-md">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const categoriesData = @json($expenseCategories);

    function toggleDinnerAttendees(selectElem) {
        const row = selectElem.closest('.grid');
        if (!row) return;
        const container = row.querySelector('.attendees-container');
        if (!container) return;
        
        const selectedText = selectElem.options[selectElem.selectedIndex]?.text || '';
        if (selectedText.toLowerCase().includes('dinner')) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            container.querySelectorAll('input').forEach(i => i.value = '');
        }
    }

    function addExpenseItemRow() {
        const container = document.getElementById('expenseItemsContainer');
        const index = container.children.length;
        
        let catOptions = categoriesData.map(c => 
            `<option value="${c.id}">${c.name}</option>`
        ).join('');

        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-gray-50 p-3 rounded-lg border border-gray-200';
        row.innerHTML = `
            <div class="md:col-span-4">
                <select name="items[${index}][expense_category_id]" required onchange="toggleDinnerAttendees(this)" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
                    <option value="">Select Category *</option>
                    ${catOptions}
                </select>
            </div>
            <div class="md:col-span-3">
                <input type="number" step="0.01" min="0.01" name="items[${index}][amount]" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-4">
                <input type="text" name="items[${index}][description]" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-1 text-right">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="attendees-container hidden col-span-1 md:col-span-12 mt-2 p-2.5 bg-blue-50/80 border border-blue-200 rounded-lg text-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-blue-900 flex items-center gap-1.5">
                        <i class="fas fa-utensils text-brand-blue"></i> Dinner Attendees / People Names (Max 5):
                    </span>
                    <span class="text-[11px] text-blue-700">Enter names of people who attended</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                    <input type="text" name="items[${index}][attendees][]" placeholder="Person 1 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                    <input type="text" name="items[${index}][attendees][]" placeholder="Person 2 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                    <input type="text" name="items[${index}][attendees][]" placeholder="Person 3 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                    <input type="text" name="items[${index}][attendees][]" placeholder="Person 4 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                    <input type="text" name="items[${index}][attendees][]" placeholder="Person 5 Name" class="w-full rounded-md border-blue-200 text-xs focus:ring-brand-blue bg-white">
                </div>
            </div>
        `;
        container.appendChild(row);
    }

    let canvas, ctx;
    let isDrawing = false;
    let hasSigned = false;

    function initSignatureCanvas() {
        canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');

        // Set line style
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Mouse events
        canvas.onmousedown = (e) => {
            isDrawing = true;
            hasSigned = true;
            const rect = canvas.getBoundingClientRect();
            ctx.beginPath();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        };
        canvas.onmousemove = (e) => {
            if (!isDrawing) return;
            const rect = canvas.getBoundingClientRect();
            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
            ctx.stroke();
        };
        canvas.onmouseup = () => isDrawing = false;
        canvas.onmouseleave = () => isDrawing = false;

        // Touch events
        canvas.ontouchstart = (e) => {
            if (e.touches.length === 1) {
                e.preventDefault();
                isDrawing = true;
                hasSigned = true;
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                ctx.beginPath();
                ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            }
        };
        canvas.ontouchmove = (e) => {
            if (isDrawing && e.touches.length === 1) {
                e.preventDefault();
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                ctx.stroke();
            }
        };
        canvas.ontouchend = () => isDrawing = false;
    }

    function clearSignatureCanvas() {
        if (!canvas || !ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSigned = false;
        const input = document.getElementById('signatureInput');
        if (input) input.value = '';
    }

    let currentApproveIsIou = false;

    function calculateApproveNotesTotal() {
        let total = 0;
        const inputs = document.querySelectorAll('.approve-note-input');
        inputs.forEach(input => {
            const val = parseFloat(input.dataset.value || 0);
            const qty = parseInt(input.value || 0);
            if (qty > 0) total += val * qty;
        });
        const coinInput = document.querySelector('.approve-coin-input');
        if (coinInput && coinInput.value) {
            total += parseFloat(coinInput.value) || 0;
        }
        const display = document.getElementById('approveMoneyNotesTotalDisplay');
        if (display) {
            display.textContent = 'Total: LKR ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }

    function calculateSettleNotesTotal() {
        let total = 0;
        const inputs = document.querySelectorAll('.settle-note-input');
        inputs.forEach(input => {
            const val = parseFloat(input.dataset.value || 0);
            const qty = parseInt(input.value || 0);
            if (qty > 0) total += val * qty;
        });
        const coinInput = document.querySelector('.settle-coin-input');
        if (coinInput && coinInput.value) {
            total += parseFloat(coinInput.value) || 0;
        }
        const display = document.getElementById('settleMoneyNotesTotalDisplay');
        if (display) {
            display.textContent = 'Total: LKR ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }

    function openAdminApproveModal(id, ref, requester, isIou = false, status = '', amount = 0) {
        currentApproveIsIou = isIou;
        document.getElementById('adminApproveForm').action = "{{ route('petty-cash.index') }}/" + id + "/admin-approve";
        const actionTitle = status === 'pending_settlement' ? 'Approve Settlement' : 'Approve Request';
        document.getElementById('adminApproveModalRef').innerHTML = `<i class="fas fa-check-double text-brand-pink mr-2"></i> ${actionTitle}: ${ref}`;
        document.getElementById('approveRequesterName').textContent = requester;

        const formattedAmount = typeof amount === 'number'
            ? amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            : parseFloat(amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const amountEl = document.getElementById('approveRequestedAmount');
        if (amountEl) {
            amountEl.textContent = 'LKR ' + formattedAmount;
        }

        document.getElementById('approveConfirmationText').textContent = status === 'pending_settlement' 
            ? 'Are you sure you want to approve the final settlement for this IOU request?' 
            : 'Are you sure you want to approve this petty cash request?';

        const banner = document.getElementById('iouMandatoryBanner');
        const reqText = document.getElementById('signatureRequiredText');
        const optText = document.getElementById('signatureOptionalText');
        if (isIou) {
            if (banner) banner.classList.remove('hidden');
            if (reqText) reqText.classList.remove('hidden');
            if (optText) optText.classList.add('hidden');
        } else {
            if (banner) banner.classList.add('hidden');
            if (reqText) reqText.classList.add('hidden');
            if (optText) optText.classList.remove('hidden');
        }

        const approveDateLabel = document.getElementById('approveDateLabel');
        const approveDateInput = document.getElementById('approveDateInput');
        const sectionTitle = document.getElementById('approveMoneyNotesSectionTitle');

        const today = new Date().toISOString().split('T')[0];
        if (approveDateInput) {
            approveDateInput.value = today;
        }

        if (status === 'pending_settlement') {
            if (approveDateLabel) approveDateLabel.innerHTML = '<i class="fas fa-calendar-check text-brand-purple mr-1"></i> IOU Settled Date *';
            if (approveDateInput) approveDateInput.name = 'settled_at';
            if (sectionTitle) sectionTitle.textContent = 'Settlement Money Notes Breakdown';

            ['5000','2000','1000','500','100','50','20'].forEach(k => {
                const el = document.getElementById('an_' + k);
                if (el) el.name = `settlement_money_notes[${k}]`;
            });
            const coinEl = document.getElementById('an_coins');
            if (coinEl) coinEl.name = 'settlement_money_notes[coins]';
        } else {
            if (approveDateLabel) {
                approveDateLabel.innerHTML = isIou 
                    ? '<i class="fas fa-calendar-plus text-brand-pink mr-1"></i> IOU Created Date *' 
                    : '<i class="fas fa-calendar-alt text-brand-blue mr-1"></i> Approval / Handover Date *';
            }
            if (approveDateInput) approveDateInput.name = 'issued_at';
            if (sectionTitle) sectionTitle.textContent = 'Money Notes Breakdown (Handed Over)';

            ['5000','2000','1000','500','100','50','20'].forEach(k => {
                const el = document.getElementById('an_' + k);
                if (el) el.name = `issued_money_notes[${k}]`;
            });
            const coinEl = document.getElementById('an_coins');
            if (coinEl) coinEl.name = 'issued_money_notes[coins]';
        }

        // Reset money breakdown input fields
        ['5000','2000','1000','500','100','50','20'].forEach(k => {
            const el = document.getElementById('an_' + k);
            if (el) el.value = '';
        });
        const coinEl = document.getElementById('an_coins');
        if (coinEl) coinEl.value = '';
        calculateApproveNotesTotal();

        document.getElementById('adminApproveModal').classList.remove('hidden');

        setTimeout(() => {
            initSignatureCanvas();
            clearSignatureCanvas();
        }, 100);
    }

    function closeAdminApproveModal() {
        document.getElementById('adminApproveModal').classList.add('hidden');
        clearSignatureCanvas();
    }

    function prepareSignatureSubmission(e) {
        if (hasSigned && canvas) {
            document.getElementById('signatureInput').value = canvas.toDataURL('image/png');
        } else {
            document.getElementById('signatureInput').value = '';
            if (currentApproveIsIou) {
                e.preventDefault();
                alert('A signature from the requested person is MANDATORY to approve or settle an IOU request.');
                return false;
            }
        }
    }

    function openSettleIouModal(id) {
        fetch("{{ route('petty-cash.index') }}/" + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('settleIouForm').action = "{{ route('petty-cash.index') }}/" + id + "/settle";
                    document.getElementById('settleIouModalRef').innerHTML = `<i class="fas fa-file-signature text-brand-purple mr-2"></i> Settle IOU Request: ${pc.reference_number}`;
                    
                    const today = new Date().toISOString().split('T')[0];
                    const dateInput = document.getElementById('settleDateInput');
                    if (dateInput) {
                        dateInput.value = pc.settled_at ? new Date(pc.settled_at).toISOString().split('T')[0] : today;
                    }

                    const noteInput = document.getElementById('settleNoteInput');
                    if (noteInput) {
                        noteInput.value = pc.extra_notes || pc.settlement_note || '';
                    }
                    const noteHiddenInput = document.getElementById('settleNoteHiddenInput');
                    if (noteHiddenInput) {
                        noteHiddenInput.value = pc.settlement_note || pc.extra_notes || '';
                    }

                    const notesObj = pc.settlement_money_notes || {};
                    ['5000','2000','1000','500','100','50','20'].forEach(k => {
                        const el = document.getElementById('sn_' + k);
                        if (el) el.value = notesObj[k] || '';
                    });
                    const coinEl = document.getElementById('sn_coins');
                    if (coinEl) coinEl.value = notesObj.coins || '';
                    calculateSettleNotesTotal();

                    const container = document.getElementById('settleItemsContainer');
                    container.innerHTML = '';

                    pc.items.forEach((item) => {
                        const div = document.createElement('div');
                        div.className = 'grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-gray-50 p-3 rounded-lg border border-gray-200';
                        div.innerHTML = `
                            <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                            <div class="md:col-span-6 font-semibold text-xs text-gray-800">
                                ${item.category ? item.category.name : 'Category'}
                                <span class="text-gray-500 font-normal block text-[11px]">${item.description || 'No note'}</span>
                            </div>
                            <div class="md:col-span-6 flex items-center gap-2">
                                <span class="text-xs text-gray-500 font-bold whitespace-nowrap">Spent LKR:</span>
                                <input type="number" step="0.01" min="0.01" name="items[${item.id}][amount]" value="${item.amount}" required class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-purple">
                            </div>
                        `;
                        container.appendChild(div);
                    });

                    document.getElementById('settleIouModal').classList.remove('hidden');
                }
            });
    }

    function openHodRejectModal(id) {
        document.getElementById('hodRejectForm').action = "{{ route('petty-cash.index') }}/" + id + "/hod-reject";
        document.getElementById('hodRejectModal').classList.remove('hidden');
    }

    function openAdminRejectModal(id) {
        document.getElementById('adminRejectForm').action = "{{ route('petty-cash.index') }}/" + id + "/admin-reject";
        document.getElementById('adminRejectModal').classList.remove('hidden');
    }

    function renderMoneyNotesBreakdownHtml(notesObj, title) {
        if (!notesObj || typeof notesObj !== 'object') return '';
        const keys = ['5000', '2000', '1000', '500', '100', '50', '20'];
        let badges = [];
        let total = 0;
        keys.forEach(k => {
            const qty = parseInt(notesObj[k] || 0);
            if (qty > 0) {
                const val = parseInt(k) * qty;
                total += val;
                badges.push(`<span class="px-2.5 py-1 bg-white border border-gray-200 text-gray-800 font-semibold rounded-md text-[11px] shadow-2xs">Rs. ${k} &times; ${qty}</span>`);
            }
        });
        const coins = parseFloat(notesObj.coins || 0);
        if (coins > 0) {
            total += coins;
            badges.push(`<span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-900 font-semibold rounded-md text-[11px] shadow-2xs">Coins: LKR ${coins.toFixed(2)}</span>`);
        }
        if (badges.length === 0) return '';

        return `
            <div class="mt-3 pt-3 border-t border-gray-200/80">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-xs font-bold text-gray-800 flex items-center">
                        <i class="fas fa-coins text-amber-500 mr-1.5"></i> ${title}
                    </h4>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Total: LKR ${total.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
                <div class="flex flex-wrap gap-1.5">${badges.join('')}</div>
            </div>
        `;
    }

    function formatDateStr(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function viewPettyCashDetails(id) {
        const voucherBtn = document.getElementById('modalVoucherLink');
        if (voucherBtn) {
            voucherBtn.href = "{{ route('petty-cash.index') }}/" + id + "/download?with_buttons=1";
        }
        fetch("{{ route('petty-cash.index') }}/" + id, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    const baseUrl = "{{ url('/') }}";
                    const createdOrIssuedDate = formatDateStr(pc.created_at);
                    const settledDateDisplay = pc.settled_at ? formatDateStr(pc.settled_at) : (pc.status === 'settled' ? 'Settled' : 'Not Settled');
                    document.getElementById('modalRef').innerHTML = `<i class="fas fa-info-circle text-brand-blue mr-2"></i> Request: ${pc.reference_number}`;
                    
                    let itemsHtml = pc.items.map(item => `
                        <tr class="border-b border-gray-100 text-sm">
                            <td class="py-2.5 px-3 font-semibold text-gray-800">${item.category ? item.category.name : 'General'}</td>
                            <td class="py-2.5 px-3 text-gray-600">${item.description || '-'}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-gray-900">LKR ${parseFloat(item.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `).join('');

                    let proofsHtml = pc.proofs && pc.proofs.length > 0 ? pc.proofs.map(p => {
                        const pUrl = p.file_path.startsWith('http') ? p.file_path : `${baseUrl}/${p.file_path.replace(/^\/?(public\/)?/, '')}`;
                        return `<a href="${pUrl}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-brand-blue rounded-lg text-xs font-semibold mr-2 mb-2">
                            <i class="fas fa-paperclip mr-1.5"></i> ${p.file_name}
                        </a>`;
                    }).join('') : '<p class="text-xs text-gray-400">No proof attachments uploaded.</p>';

                    let sigUrl = pc.signature_path ? (pc.signature_path.startsWith('data:image/') ? pc.signature_path : `${baseUrl}/${pc.signature_path.replace(/^\/?(public\/)?/, '')}`) : '';
                    let signatureHtml = pc.signature_path ? `
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-signature text-brand-purple mr-1.5"></i> ${pc.is_iou ? 'IOU Issued Signature (Money Handed Over)' : 'Approved Signature'}
                            </h4>
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 inline-block">
                                <img src="${sigUrl}" alt="Approved Signature" class="max-h-24 max-w-full object-contain rounded">
                            </div>
                        </div>
                    ` : '';

                    let settleSigUrl = (pc.is_iou && pc.settlement_signature_path) ? (pc.settlement_signature_path.startsWith('data:image/') ? pc.settlement_signature_path : `${baseUrl}/${pc.settlement_signature_path.replace(/^\/?(public\/)?/, '')}`) : '';
                    let settlementSignatureHtml = (pc.is_iou && pc.settlement_signature_path) ? `
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <h4 class="text-xs sm:text-sm font-bold text-emerald-800 mb-2 flex items-center">
                                <i class="fas fa-signature text-emerald-600 mr-1.5"></i> Settlement Approved Signature (IOU Settled)
                            </h4>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 inline-block">
                                <img src="${settleSigUrl}" alt="Settlement Signature" class="max-h-24 max-w-full object-contain rounded">
                            </div>
                        </div>
                    ` : '';

                    let notesHtml = '';
                    if (pc.extra_notes) {
                        notesHtml += `<div class="p-3 bg-amber-50/80 border border-amber-200 rounded-lg text-xs text-amber-900 mb-2"><strong class="flex items-center gap-1"><i class="fas fa-comment-alt text-amber-700 mr-1"></i> Extra Notes / Remarks:</strong><p class="mt-1 whitespace-pre-line text-gray-800">${pc.extra_notes}</p></div>`;
                    }
                    if (pc.hod_rejection_note) {
                        notesHtml += `<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800 mb-2"><strong>HOD Rejection Note:</strong> ${pc.hod_rejection_note}</div>`;
                    }
                    if (pc.admin_rejection_note) {
                        notesHtml += `<div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800 mb-2"><strong>Finance Rejection Note:</strong> ${pc.admin_rejection_note}</div>`;
                    }
                    if (pc.settlement_note) {
                        notesHtml += `<div class="p-3 bg-purple-50 border border-purple-200 rounded-lg text-xs text-purple-900 mb-2"><strong><i class="fas fa-sticky-note text-brand-purple mr-1"></i> IOU Settlement Note:</strong> ${pc.settlement_note}</div>`;
                    }

                    let issuedNotesHtml = renderMoneyNotesBreakdownHtml(pc.issued_money_notes, pc.is_iou ? 'IOU Money Handed Over Breakdown' : 'Approval Money Notes Breakdown');
                    let settlementNotesHtml = renderMoneyNotesBreakdownHtml(pc.settlement_money_notes, 'IOU Settlement Money Notes Breakdown');

                    let iouPolicyBannerHtml = '';
                    if (pc.is_iou && pc.status !== 'settled') {
                        iouPolicyBannerHtml = `
                            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 shadow-2xs">
                                <div>
                                    <strong class="flex items-center text-amber-900 font-bold text-xs sm:text-sm">
                                        <i class="fas fa-clock text-amber-600 mr-1.5"></i> 72-Hour IOU Settlement Policy Notice
                                    </strong>
                                    <p class="mt-0.5 text-amber-800 text-xs">
                                        This IOU must be settled with expenditure proofs & receipts <strong>within 72 hours of approval</strong>.
                                    </p>
                                </div>
                                <form action="{{ route('petty-cash.index') }}/${pc.id}/remind-iou" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-xs shadow transition-colors flex items-center gap-1.5">
                                        <i class="fas fa-paper-plane"></i> Send Email Reminder
                                    </button>
                                </form>
                            </div>
                        `;
                    }

                    document.getElementById('modalBody').innerHTML = `
                        ${iouPolicyBannerHtml}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl text-xs">
                            <div><span class="text-gray-500 block">Requested By:</span><strong class="text-gray-800 text-sm">${pc.user ? pc.user.name : '-'}</strong></div>
                            <div><span class="text-gray-500 block">Department:</span><strong class="text-gray-800 text-sm">${pc.department || '-'}</strong></div>
                            <div><span class="text-gray-500 block">HOD:</span><strong class="text-gray-800 text-sm">${pc.hod ? pc.hod.name : 'Not Assigned'}</strong></div>
                            <div><span class="text-gray-500 block">Job Number:</span><strong class="text-gray-800 text-sm font-mono">${pc.job_number || '-'}</strong></div>
                            
                            <div><span class="text-gray-500 block">${pc.is_iou ? 'IOU Created Date:' : 'Approval Date:'}</span><strong class="text-gray-800 text-sm font-semibold text-brand-purple">${createdOrIssuedDate}</strong></div>
                            ${pc.is_iou ? `<div><span class="text-gray-500 block">IOU Settled Date:</span><strong class="text-gray-800 text-sm font-semibold text-emerald-700">${settledDateDisplay}</strong></div>` : ''}
                        </div>

                        ${notesHtml}

                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-2">Expense Line Items</h4>
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 text-xs font-semibold text-gray-600 uppercase">
                                        <th class="py-2 px-3">Category</th>
                                        <th class="py-2 px-3">Description</th>
                                        <th class="py-2 px-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 font-bold">
                                        <td colspan="2" class="py-2.5 px-3 text-right text-gray-700">Total:</td>
                                        <td class="py-2.5 px-3 text-right text-brand-pink">LKR ${parseFloat(pc.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        ${issuedNotesHtml}
                        ${settlementNotesHtml}

                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-2 mt-4">Proof Attachments</h4>
                            <div class="flex flex-wrap">${proofsHtml}</div>
                        </div>

                        ${signatureHtml}
                        ${settlementSignatureHtml}
                    `;

                    document.getElementById('detailsModal').classList.remove('hidden');
                }
            });
    }

    function openReappealModal(id) {
        fetch("{{ route('petty-cash.index') }}/" + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('reappealForm').action = "{{ route('petty-cash.index') }}/" + id + "/reappeal";
                    if (pc.hod_id) document.getElementById('reappeal_hod_id').value = pc.hod_id;
                    if (pc.job_number) {
                        if (typeof reappealJobTs !== 'undefined' && reappealJobTs) reappealJobTs.setValue(pc.job_number);
                        else document.getElementById('reappeal_job_number').value = pc.job_number;
                    } else {
                        if (typeof reappealJobTs !== 'undefined' && reappealJobTs) reappealJobTs.setValue('');
                        else document.getElementById('reappeal_job_number').value = '';
                    }
                    const reappealNotesInput = document.getElementById('reappealExtraNotes');
                    if (reappealNotesInput) reappealNotesInput.value = pc.extra_notes || '';

                    const container = document.getElementById('reappealItemsContainer');
                    container.innerHTML = '';

                    pc.items.forEach((item, index) => {
                        addReappealItemRow(item.expense_category_id, item.amount, item.description);
                    });

                    document.getElementById('reappealModal').classList.remove('hidden');
                }
            });
    }

    function addReappealItemRow(catId = '', amount = '', desc = '') {
        const container = document.getElementById('reappealItemsContainer');
        const index = container.children.length;
        
        let catOptions = categoriesData.map(c => 
            `<option value="${c.id}" ${c.id == catId ? 'selected' : ''}>${c.name}</option>`
        ).join('');

        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-gray-50 p-3 rounded-lg border border-gray-200';
        row.innerHTML = `
            <div class="md:col-span-4">
                <select name="items[${index}][expense_category_id]" required class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
                    <option value="">Select Category *</option>
                    ${catOptions}
                </select>
            </div>
            <div class="md:col-span-3">
                <input type="number" step="0.01" min="0.01" name="items[${index}][amount]" value="${amount}" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-4">
                <input type="text" name="items[${index}][description]" value="${desc}" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-1 text-right">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    function validateProofFile(input) {
        if (!input || !input.files || !input.files[0]) return true;
        
        const file = input.files[0];
        const maxSizeBytes = 10 * 1024 * 1024; // 10MB
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        const fileName = file.name || 'Selected file';
        const fileExt = fileName.includes('.') ? fileName.split('.').pop().toLowerCase() : '';

        // Validate format extension
        if (!allowedExtensions.includes(fileExt)) {
            input.value = ''; // Reset invalid input
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid File Format',
                    html: `The file <strong>"${fileName}"</strong> is not in a supported format.<br><br>Allowed formats: <strong>PNG, JPG, JPEG, PDF, DOC, DOCX</strong>.`,
                    confirmButtonColor: '#ec4899',
                    confirmButtonText: 'OK'
                });
            } else {
                alert(`Invalid File Format: "${fileName}". Allowed formats are PNG, JPG, JPEG, PDF, DOC, DOCX.`);
            }
            return false;
        }

        // Validate size limit (10MB)
        if (file.size > maxSizeBytes) {
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            input.value = ''; // Reset invalid input
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    html: `The file <strong>"${fileName}"</strong> (${fileSizeMB} MB) exceeds the maximum allowed size limit of <strong>10MB</strong>.`,
                    confirmButtonColor: '#ec4899',
                    confirmButtonText: 'OK'
                });
            } else {
                alert(`File Too Large: "${fileName}" (${fileSizeMB} MB) exceeds the 10MB limit.`);
            }
            return false;
        }

        return true;
    }

    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches && e.target.matches('input[type="file"][name="proofs[]"]')) {
            validateProofFile(e.target);
        }
    });

    function addProofFileInput(containerId) {
        const container = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 mt-2';
        div.innerHTML = `
            <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-brand-blue hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-1 flex-shrink-0">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    let editItemIndex = 0;

    function toggleEditDateFields() {
        let isIou = window.currentEditIsIou || false;
        const selects = document.querySelectorAll('#editExpenseItemsContainer select');
        selects.forEach(select => {
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.text && selectedOpt.text.toUpperCase().includes('IOU')) {
                isIou = true;
            }
        });

        const statusSelect = document.getElementById('editStatus');
        if (statusSelect && ['iou_issued', 'pending_settlement', 'settled'].includes(statusSelect.value)) {
            isIou = true;
        }

        const settledGroup = document.getElementById('editSettledAtGroup');
        const issuedLabel = document.getElementById('editIssuedAtLabel');

        if (settledGroup) {
            if (isIou) {
                settledGroup.classList.remove('hidden');
            } else {
                settledGroup.classList.add('hidden');
                const settledInput = document.getElementById('editSettledAt');
                if (settledInput) settledInput.value = '';
            }
        }

        if (issuedLabel) {
            issuedLabel.innerHTML = isIou 
                ? '<i class="fas fa-calendar-check text-brand-purple mr-1"></i> IOU Handover Date' 
                : '<i class="fas fa-calendar-check text-brand-purple mr-1"></i> Approval Date';
        }
    }

    function addEditExpenseItemRow(catId = '', amount = '', desc = '') {
        const container = document.getElementById('editExpenseItemsContainer');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-gray-50 p-2.5 rounded-lg border border-gray-200';

        let catOptions = `<option value="">Select Category *</option>` + categoriesData.map(c => 
            `<option value="${c.id}" ${c.id == catId ? 'selected' : ''}>${c.name}</option>`
        ).join('');

        div.innerHTML = `
            <div class="md:col-span-4">
                <select name="items[${editItemIndex}][expense_category_id]" required class="w-full rounded-md border-gray-300 text-xs focus:ring-amber-500" onchange="toggleEditDateFields()">
                    ${catOptions}
                </select>
            </div>
            <div class="md:col-span-3">
                <input type="number" step="0.01" min="0.01" name="items[${editItemIndex}][amount]" value="${amount}" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-xs focus:ring-amber-500">
            </div>
            <div class="md:col-span-4">
                <input type="text" name="items[${editItemIndex}][description]" value="${desc || ''}" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-xs focus:ring-amber-500">
            </div>
            <div class="md:col-span-1 text-right">
                <button type="button" onclick="if(document.querySelectorAll('#editExpenseItemsContainer > div').length > 1) this.closest('.grid').remove()" class="text-red-500 hover:text-red-700 p-1">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
        editItemIndex++;
    }

    function openEditPettyCashModal(id) {
        fetch("{{ route('petty-cash.index') }}/" + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('editPettyCashForm').action = "{{ route('petty-cash.index') }}/" + id;
                    document.getElementById('editModalRef').innerHTML = `<i class="fas fa-edit text-amber-600 mr-2"></i> Edit Petty Cash Request: ${pc.reference_number}`;
                    document.getElementById('editRequesterName').textContent = pc.user ? pc.user.name : 'Staff';
                    document.getElementById('editRefDisplay').textContent = pc.reference_number;

                    const hodSelect = document.getElementById('editHodId');
                    if (hodSelect) hodSelect.value = pc.hod_id;

                    const jobSelect = document.getElementById('editJobNumber');
                    if (jobSelect) {
                        jobSelect.value = pc.job_number || '';
                        if (typeof editJobTs !== 'undefined' && editJobTs) editJobTs.setValue(pc.job_number || '');
                    }

                    const statusSelect = document.getElementById('editStatus');
                    if (statusSelect) statusSelect.value = pc.status;

                    const extraNotesInput = document.getElementById('editExtraNotes');
                    if (extraNotesInput) extraNotesInput.value = pc.extra_notes || '';

                    const createdAtInput = document.getElementById('editCreatedAt');
                    if (createdAtInput) createdAtInput.value = pc.created_at ? pc.created_at.substring(0, 10) : '';

                    const issuedAtInput = document.getElementById('editIssuedAt');
                    if (issuedAtInput) issuedAtInput.value = pc.issued_at ? pc.issued_at.substring(0, 10) : '';

                    const settledAtInput = document.getElementById('editSettledAt');
                    if (settledAtInput) settledAtInput.value = pc.settled_at ? pc.settled_at.substring(0, 10) : '';

                    window.currentEditIsIou = pc.is_iou;
                    toggleEditDateFields();

                    // Populate line items
                    const container = document.getElementById('editExpenseItemsContainer');
                    container.innerHTML = '';
                    editItemIndex = 0;

                    if (pc.items && pc.items.length > 0) {
                        pc.items.forEach(item => {
                            addEditExpenseItemRow(item.expense_category_id, item.amount, item.description);
                        });
                    } else {
                        addEditExpenseItemRow();
                    }

                    // Populate existing proofs
                    const proofsContainer = document.getElementById('editExistingProofsContainer');
                    proofsContainer.innerHTML = '';
                    if (pc.proofs && pc.proofs.length > 0) {
                        pc.proofs.forEach(proof => {
                            const div = document.createElement('div');
                            div.className = 'flex items-center justify-between bg-white p-2 rounded border border-gray-200 text-xs';
                            div.innerHTML = `
                                <a href="{{ url('/') }}/${proof.file_path}" target="_blank" class="text-brand-blue hover:underline flex items-center gap-1.5 truncate">
                                    <i class="fas fa-paperclip text-gray-400"></i> ${proof.file_name || 'Proof File'}
                                </a>
                                <label class="flex items-center gap-1 text-red-600 text-[11px] font-semibold cursor-pointer whitespace-nowrap ml-2">
                                    <input type="checkbox" name="delete_proofs[]" value="${proof.id}" class="rounded text-red-600 focus:ring-red-500"> Delete
                                </label>
                            `;
                            proofsContainer.appendChild(div);
                        });
                    } else {
                        proofsContainer.innerHTML = '<p class="text-xs text-gray-400 italic">No existing proofs attached.</p>';
                    }

                    // Reset new proof container
                    const newProofContainer = document.getElementById('editProofContainer');
                    newProofContainer.innerHTML = `
                        <div class="flex items-center gap-2">
                            <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 border border-gray-200 rounded-lg p-1">
                            <button type="button" onclick="if(document.querySelectorAll('#editProofContainer > div').length > 1) this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-xs px-1 flex-shrink-0">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;

                    document.getElementById('editPettyCashModal').classList.remove('hidden');
                }
            });
    }

    @if(request('approve_id'))
        @php $autoPc = \App\Models\PettyCashRequest::with('user')->find(request('approve_id')); @endphp
        @if($autoPc)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                openAdminApproveModal(
                    {{ $autoPc->id }},
                    '{{ $autoPc->reference_number }}',
                    '{{ addslashes($autoPc->user->name ?? "Staff") }}',
                    {{ $autoPc->isIOU() ? 'true' : 'false' }},
                    '{{ $autoPc->status }}',
                    {{ $autoPc->total_amount }}
                );
            }, 200);
        });
        @endif
    @elseif(request('reject_id'))
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() { openAdminRejectModal({{ request('reject_id') }}); }, 200);
        });
    @elseif(request('hod_reject_id'))
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() { openHodRejectModal({{ request('hod_reject_id') }}); }, 200);
        });
    @elseif(request('settle_id'))
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() { openSettleIouModal({{ request('settle_id') }}); }, 200);
        });
    @elseif(request('reappeal_id'))
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() { openReappealModal({{ request('reappeal_id') }}); }, 200);
        });
    @endif
    function confirmDeletePettyCash(event, formElement, refNumber) {
        event.preventDefault();
        Swal.fire({
            title: 'Delete Petty Cash Request?',
            html: `Are you sure you want to delete request <strong class="font-mono text-gray-900 whitespace-nowrap">${refNumber}</strong>?<br><span class="text-xs text-red-600 font-medium mt-1.5 block">This will permanently remove all associated items and proofs.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#4b5563',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1.5"></i> Yes, Delete Request',
            cancelButtonText: 'Cancel',
            buttonsStyling: true,
            width: '420px',
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-gray-100 p-5',
                title: 'text-base font-bold text-gray-800 mt-2',
                htmlContainer: 'text-xs text-gray-600 mt-1',
                confirmButton: 'px-4 py-2 rounded-lg font-bold text-xs text-white shadow-md transition-all mr-2',
                cancelButton: 'px-4 py-2 rounded-lg font-semibold text-xs text-gray-700 bg-gray-200 hover:bg-gray-300 transition-all'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                formElement.submit();
            }
        });
        return false;
    }

    function handleNewRequestClick(e) {
        if (e) e.preventDefault();
        const activeIouRef = "{{ $unsettledIou ? $unsettledIou->reference_number : '' }}";
        const activeIouData = @json($unsettledIou);

        if (activeIouRef) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Active Unsettled IOU Detected',
                    html: `You cannot submit a new petty cash request because you currently have an active unsettled IOU (<strong>${activeIouRef}</strong>).<br><br>According to company policy, you must settle your existing IOU before requesting new petty cash.`,
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-receipt mr-1"></i> Settle IOU Now',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed && activeIouData) {
                        openSettleIouModal(activeIouData);
                    }
                });
            } else {
                alert(`You have an active unsettled IOU (${activeIouRef}). Please settle it first.`);
            }
            return false;
        }

        document.getElementById('newPettyCashModal').classList.remove('hidden');
        if (typeof createJobTs !== 'undefined' && createJobTs) createJobTs.setValue('');
    }

    let createJobTs, reappealJobTs, editJobTs;
    document.addEventListener('DOMContentLoaded', function() {
        const tsConfig = {
            create: false,
            placeholder: '-- Select Job Number (Optional) --',
            allowEmptyOption: true,
            plugins: ['dropdown_input']
        };
        if (document.getElementById('create_job_number')) {
            createJobTs = new TomSelect('#create_job_number', tsConfig);
        }
        if (document.getElementById('reappeal_job_number')) {
            reappealJobTs = new TomSelect('#reappeal_job_number', tsConfig);
        }
        if (document.getElementById('editJobNumber')) {
            editJobTs = new TomSelect('#editJobNumber', tsConfig);
        }
    });
</script>
@endpush
@endsection
