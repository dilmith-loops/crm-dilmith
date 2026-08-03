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
            <button onclick="document.getElementById('newPettyCashModal').classList.remove('hidden')"
                class="w-full md:w-auto px-5 py-2.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white rounded-lg hover:opacity-90 font-medium transition-all flex items-center justify-center shadow-md">
                <i class="fas fa-plus mr-2"></i> New Petty Cash Request
            </button>
        </div>
    </div>

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
            Pending Super Admin
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
            Rejected by Admin
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
                                        <i class="fas fa-user-shield mr-1"></i> Pending Admin
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
                                        <i class="fas fa-ban mr-1"></i> Rejected by Admin
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <button onclick="viewPettyCashDetails({{ $pc->id }})"
                                        class="px-2.5 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </button>

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
                                            <button onclick="openAdminApproveModal({{ $pc->id }}, '{{ $pc->reference_number }}', '{{ addslashes($pc->user->name ?? 'Staff') }}', {{ $pc->isIOU() ? 'true' : 'false' }}, '{{ $pc->status }}')"
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
                    <select name="job_number" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-blue focus:ring-brand-blue">
                        <option value="">-- Select Job Number (Optional) --</option>
                        @foreach($jobs as $jobNo)
                            <option value="{{ $jobNo }}">{{ $jobNo }}</option>
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
                            <select name="items[0][expense_category_id]" required class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
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
                    </div>
                </div>
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
        <div class="flex justify-end pt-4 border-t border-gray-100 mt-6">
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')"
                class="px-5 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition-colors">
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
    <div class="relative my-auto p-5 sm:p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white max-h-[90vh] overflow-y-auto">
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

            <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3.5 text-xs text-blue-900 space-y-1">
                <p><strong>Requester:</strong> <span id="approveRequesterName">-</span></p>
                <p class="text-blue-700" id="approveConfirmationText">Are you sure you want to approve this petty cash request?</p>
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
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-900 flex items-start gap-2">
                <i class="fas fa-info-circle text-brand-purple text-base mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong>IOU Settlement Process:</strong> Upload your expenditure proofs (receipts, bills, or invoices) and confirm the final spent line item amounts. Once submitted, Super Admin will review and approve the settlement.
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-800 mb-2">Final Expenditure Line Items & Amounts</label>
                <div id="settleItemsContainer" class="space-y-3">
                    <!-- Dynamic JS content -->
                </div>
            </div>

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
                <i class="fas fa-ban mr-2"></i> Reject Petty Cash Request (Super Admin)
            </h3>
            <button onclick="document.getElementById('adminRejectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="adminRejectForm" action="" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Super Admin Rejection Reason *</label>
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
                        @foreach($jobs as $jobNo)
                            <option value="{{ $jobNo }}">{{ $jobNo }}</option>
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

@push('scripts')
<script>
    const categoriesData = @json($expenseCategories);

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
                <select name="items[${index}][expense_category_id]" required class="w-full rounded-md border-gray-300 text-xs focus:ring-brand-blue">
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

    function openAdminApproveModal(id, ref, requester, isIou = false, status = '') {
        currentApproveIsIou = isIou;
        document.getElementById('adminApproveForm').action = `/petty-cash/${id}/admin-approve`;
        const actionTitle = status === 'pending_settlement' ? 'Approve Settlement' : 'Approve Request';
        document.getElementById('adminApproveModalRef').innerHTML = `<i class="fas fa-check-double text-brand-pink mr-2"></i> ${actionTitle}: ${ref}`;
        document.getElementById('approveRequesterName').textContent = requester;
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
        fetch(`/petty-cash/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('settleIouForm').action = `/petty-cash/${id}/settle`;
                    document.getElementById('settleIouModalRef').innerHTML = `<i class="fas fa-file-signature text-brand-purple mr-2"></i> Settle IOU Request: ${pc.reference_number}`;
                    
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
        document.getElementById('hodRejectForm').action = `/petty-cash/${id}/hod-reject`;
        document.getElementById('hodRejectModal').classList.remove('hidden');
    }

    function openAdminRejectModal(id) {
        document.getElementById('adminRejectForm').action = `/petty-cash/${id}/admin-reject`;
        document.getElementById('adminRejectModal').classList.remove('hidden');
    }

    function viewPettyCashDetails(id) {
        fetch(`/petty-cash/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('modalRef').innerHTML = `<i class="fas fa-info-circle text-brand-blue mr-2"></i> Request: ${pc.reference_number}`;
                    
                    let itemsHtml = pc.items.map(item => `
                        <tr class="border-b border-gray-100 text-sm">
                            <td class="py-2.5 px-3 font-semibold text-gray-800">${item.category ? item.category.name : 'General'}</td>
                            <td class="py-2.5 px-3 text-gray-600">${item.description || '-'}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-gray-900">LKR ${parseFloat(item.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `).join('');

                    let proofsHtml = pc.proofs && pc.proofs.length > 0 ? pc.proofs.map(p => `
                        <a href="/${p.file_path}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-brand-blue rounded-lg text-xs font-semibold mr-2 mb-2">
                            <i class="fas fa-paperclip mr-1.5"></i> ${p.file_name}
                        </a>
                    `).join('') : '<p class="text-xs text-gray-400">No proof attachments uploaded.</p>';

                    let signatureHtml = pc.signature_path ? `
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-signature text-brand-purple mr-1.5"></i> Initial Approved Signature (Money Handed Over)
                            </h4>
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 inline-block">
                                <img src="/${pc.signature_path}" alt="Approved Signature" class="max-h-24 max-w-full object-contain rounded">
                            </div>
                        </div>
                    ` : '';

                    let settlementSignatureHtml = pc.settlement_signature_path ? `
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <h4 class="text-xs sm:text-sm font-bold text-emerald-800 mb-2 flex items-center">
                                <i class="fas fa-signature text-emerald-600 mr-1.5"></i> Settlement Approved Signature (IOU Settled)
                            </h4>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 inline-block">
                                <img src="/${pc.settlement_signature_path}" alt="Settlement Signature" class="max-h-24 max-w-full object-contain rounded">
                            </div>
                        </div>
                    ` : '';

                    let notesHtml = '';
                    if (pc.hod_rejection_note) {
                        notesHtml += `<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800"><strong>HOD Rejection Note:</strong> ${pc.hod_rejection_note}</div>`;
                    }
                    if (pc.admin_rejection_note) {
                        notesHtml += `<div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800 mt-2"><strong>Super Admin Rejection Note:</strong> ${pc.admin_rejection_note}</div>`;
                    }

                    document.getElementById('modalBody').innerHTML = `
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl text-xs">
                            <div><span class="text-gray-500 block">Requested By:</span><strong class="text-gray-800 text-sm">${pc.user ? pc.user.name : '-'}</strong></div>
                            <div><span class="text-gray-500 block">Department:</span><strong class="text-gray-800 text-sm">${pc.department || '-'}</strong></div>
                            <div><span class="text-gray-500 block">HOD:</span><strong class="text-gray-800 text-sm">${pc.hod ? pc.hod.name : 'Not Assigned'}</strong></div>
                            <div><span class="text-gray-500 block">Job Number:</span><strong class="text-gray-800 text-sm font-mono">${pc.job_number || '-'}</strong></div>
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

                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-2">Proof Attachments</h4>
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
        fetch(`/petty-cash/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('reappealForm').action = `/petty-cash/${id}/reappeal`;
                    if (pc.hod_id) document.getElementById('reappeal_hod_id').value = pc.hod_id;
                    if (pc.job_number) document.getElementById('reappeal_job_number').value = pc.job_number;

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
</script>
@endpush
@endsection
