@extends('layouts.app')

@section('header', 'Staff Dashboard')

@section('content')
<div class="max-w-5xl mx-auto py-4 sm:py-8 px-3 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-brand-pink via-brand-purple to-brand-blue rounded-2xl p-5 sm:p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row justify-between items-stretch md:items-center gap-5 sm:gap-6">
        <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none">
            <i class="fas fa-id-card text-8xl sm:text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start space-y-3 sm:space-y-0 sm:space-x-5 md:space-x-6 text-center sm:text-left">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&size=128"
                 alt="{{ $user->name }}" 
                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-white/30 shadow-lg object-cover flex-shrink-0">
            <div>
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[11px] sm:text-xs font-semibold uppercase tracking-wider mb-2 text-white">
                    <i class="fas fa-user-tag mr-1"></i> Staff Member
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight sm:text-4xl">
                    Welcome, {{ $user->name }}
                </h1>
                <p class="text-white/80 text-xs sm:text-sm mt-1">
                    Staff Portal & Department Information
                </p>
            </div>
        </div>
        <!-- Petty Cash Action Button -->
        <div class="relative z-10">
            <button onclick="document.getElementById('newPettyCashModal').classList.remove('hidden')"
                class="w-full md:w-auto px-6 py-3 bg-white text-brand-purple hover:bg-gray-50 font-bold rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center">
                <i class="fas fa-wallet mr-2 text-brand-pink text-lg"></i> Petty Cash Request
            </button>
        </div>
    </div>

    <!-- Staff Information Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
        <!-- Name Card -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-md border border-gray-100 flex items-center space-x-3.5 sm:space-x-4 hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-pink-50 flex items-center justify-center text-brand-pink text-xl sm:text-2xl flex-shrink-0">
                <i class="fas fa-user"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 break-words leading-snug mt-0.5 sm:mt-1">{{ $user->name }}</p>
            </div>
        </div>

        <!-- Department Card -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-md border border-gray-100 flex items-center space-x-3.5 sm:space-x-4 hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-purple-50 flex items-center justify-center text-brand-purple text-xl sm:text-2xl flex-shrink-0">
                <i class="fas fa-building"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">Department</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 break-words leading-snug mt-0.5 sm:mt-1">{{ $user->department ?: 'Not Assigned' }}</p>
            </div>
        </div>

        <!-- HOD Name Card -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-md border border-gray-100 flex items-center space-x-3.5 sm:space-x-4 hover:shadow-lg transition-shadow sm:col-span-2 md:col-span-1">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-blue-50 flex items-center justify-center text-brand-blue text-xl sm:text-2xl flex-shrink-0">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider">HOD Name</p>
                <p class="text-base sm:text-lg font-bold text-gray-900 break-words leading-snug mt-0.5 sm:mt-1">{{ $hodName }}</p>
            </div>
        </div>
    </div>

    <!-- Petty Cash Requests Table -->
    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-800">My Petty Cash Requests</h2>
                <p class="text-xs text-gray-500">Track and manage your submitted expenditure requests.</p>
            </div>
            <button onclick="document.getElementById('newPettyCashModal').classList.remove('hidden')"
                class="w-full sm:w-auto justify-center px-4 py-2.5 sm:py-2 bg-brand-pink text-white text-xs font-semibold rounded-lg hover:bg-brand-purple transition-all flex items-center shadow-sm">
                <i class="fas fa-plus mr-1.5"></i> New Request
            </button>
        </div>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-left border-collapse min-w-[600px] sm:min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                        <th class="py-3.5 px-4 sm:px-6">Ref #</th>
                        <th class="py-3.5 px-4 sm:px-6">HOD Name</th>
                        <th class="py-3.5 px-4 sm:px-6">Job Number</th>
                        <th class="py-3.5 px-4 sm:px-6">Total Amount</th>
                        <th class="py-3.5 px-4 sm:px-6">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                    @forelse($pettyCashes as $pc)
                        <tr class="hover:bg-gray-50/50 transition-colors whitespace-nowrap">
                            <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-gray-900">{{ $pc->reference_number }}</td>
                            <td class="py-3.5 px-4 sm:px-6 font-medium text-gray-800">{{ $pc->hod->name ?? 'Not Assigned' }}</td>
                            <td class="py-3.5 px-4 sm:px-6 font-mono text-xs text-gray-600">{{ $pc->job_number ?: '-' }}</td>
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-gray-900">LKR {{ number_format($pc->total_amount, 2) }}</td>
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                @if($pc->status === 'pending_hod')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 inline-flex items-center whitespace-nowrap">
                                        Pending HOD
                                    </span>
                                @elseif($pc->status === 'pending_super_admin')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 inline-flex items-center whitespace-nowrap">
                                        Pending Admin
                                    </span>
                                @elseif($pc->status === 'approved')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 inline-flex items-center whitespace-nowrap">
                                        Approved
                                    </span>
                                @elseif($pc->status === 'iou_issued')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-300 inline-flex items-center whitespace-nowrap" title="Money Handed Over - IOU Unsettled">
                                        <i class="fas fa-hand-holding-usd mr-1"></i> Approved (IOU Unsettled)
                                    </span>
                                @elseif($pc->status === 'pending_settlement')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-300 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Settlement Pending
                                    </span>
                                @elseif($pc->status === 'settled')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center whitespace-nowrap">
                                        <i class="fas fa-check-double mr-1"></i> IOU Settled
                                    </span>
                                @elseif($pc->status === 'rejected_by_hod')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 inline-flex items-center whitespace-nowrap" title="{{ $pc->hod_rejection_note }}">
                                        Rejected by HOD
                                    </span>
                                @elseif($pc->status === 'rejected_by_super_admin')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 inline-flex items-center whitespace-nowrap" title="{{ $pc->admin_rejection_note }}">
                                        Rejected by Admin
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap space-x-2">
                                <button onclick="viewPettyCashDetails({{ $pc->id }})"
                                    class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                                    Details
                                </button>
                                @if($pc->status === 'iou_issued')
                                    <button onclick="openSettleIouModal({{ $pc->id }})"
                                        class="px-3 py-1.5 bg-brand-purple text-white text-xs font-bold rounded-lg hover:bg-brand-pink transition-colors inline-flex items-center shadow-sm">
                                        <i class="fas fa-file-signature mr-1"></i> Settle IOU
                                    </button>
                                @endif
                                @if(in_array($pc->status, ['rejected_by_hod', 'rejected_by_super_admin']))
                                    <button onclick="openReappealModal({{ $pc->id }})"
                                        class="px-3 py-1.5 bg-brand-blue text-white text-xs font-semibold rounded-lg hover:bg-brand-purple transition-colors">
                                        Re-appeal
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                No Petty Cash requests submitted yet. Click "Petty Cash Request" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Petty Cash Request Modal -->
<div id="newPettyCashModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-4 sm:p-6 border w-full max-w-3xl shadow-2xl rounded-xl sm:rounded-2xl bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3.5 border-b border-gray-200">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-wallet text-brand-pink mr-2 text-base sm:text-lg"></i> New Petty Cash Request
            </h3>
            <button onclick="document.getElementById('newPettyCashModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>
        <form action="{{ route('petty-cash.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-5 sm:space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">HOD Associated With *</label>
                    <select name="hod_id" required class="w-full rounded-lg border-gray-300 text-base sm:text-sm focus:border-brand-blue focus:ring-brand-blue">
                        @foreach($hods as $h)
                            <option value="{{ $h->id }}" {{ $user->supervisor_id == $h->id ? 'selected' : '' }}>
                                {{ $h->name }} ({{ $h->department ?: 'HOD' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Job Number</label>
                    <select name="job_number" class="w-full rounded-lg border-gray-300 text-base sm:text-sm focus:border-brand-blue focus:ring-brand-blue">
                        <option value="">-- Select Job Number (Optional) --</option>
                        @foreach($jobs as $jobNo)
                            <option value="{{ $jobNo }}">{{ $jobNo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs sm:text-sm font-bold text-gray-800">Expense Line Items *</label>
                    <button type="button" onclick="addExpenseItemRow()" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple transition-all font-semibold">
                        <i class="fas fa-plus mr-1"></i> Add Line Item
                    </button>
                </div>
                <div id="expenseItemsContainer" class="space-y-3">
                    <!-- Initial Row -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="md:col-span-4">
                            <select name="items[0][expense_category_id]" required class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
                                <option value="">Select Category *</option>
                                @foreach($expenseCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <input type="number" step="0.01" min="0.01" name="items[0][amount]" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
                        </div>
                        <div class="md:col-span-4">
                            <input type="text" name="items[0][description]" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
                        </div>
                        <div class="md:col-span-1 flex justify-end md:justify-center pt-1 md:pt-0">
                            <button type="button" onclick="if(document.querySelectorAll('#expenseItemsContainer .grid').length > 1) this.closest('.grid').remove()" class="text-red-500 hover:text-red-700 text-xs py-1 flex items-center gap-1 font-semibold">
                                <i class="fas fa-trash"></i> <span class="md:hidden">Remove</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs sm:text-sm font-bold text-gray-800">Proofs of Expenditure</label>
                    <button type="button" onclick="addProofFileInput('newProofContainerStaff')" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple transition-all flex items-center font-semibold">
                        <i class="fas fa-plus mr-1"></i> Add File
                    </button>
                </div>
                <div id="newProofContainerStaff" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-brand-blue hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1">
                        <button type="button" onclick="if(document.querySelectorAll('#newProofContainerStaff > div').length > 1) this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-1 flex-shrink-0">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">Upload receipts, bills, or invoices (PNG, JPG, PDF, DOCX). Click "Add File" to select multiple files.</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('newPettyCashModal').classList.add('hidden')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 text-sm">
                    Cancel
                </button>
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white font-medium rounded-lg hover:opacity-90 shadow-md text-sm">
                    Submit to HOD
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-4 sm:p-6 border w-full max-w-3xl shadow-2xl rounded-xl sm:rounded-2xl bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3.5 border-b border-gray-200">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center" id="modalRef">
                <i class="fas fa-info-circle text-brand-blue mr-2 text-base sm:text-lg"></i> Request Details
            </h3>
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>
        <div class="mt-4 space-y-5 sm:space-y-6" id="modalBody">
            <!-- Dynamic Data inserted by JS -->
        </div>
        <div class="flex justify-end pt-4 border-t border-gray-100 mt-6">
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')"
                class="w-full sm:w-auto px-5 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition-colors text-sm">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Re-appeal Modal -->
<div id="reappealModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-4 sm:p-6 border w-full max-w-3xl shadow-2xl rounded-xl sm:rounded-2xl bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3.5 border-b border-gray-200">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-redo text-brand-blue mr-2 text-base sm:text-lg"></i> Re-appeal Petty Cash Request
            </h3>
            <button onclick="document.getElementById('reappealModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>
        <form id="reappealForm" action="" method="POST" enctype="multipart/form-data" class="mt-4 space-y-5 sm:space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">HOD Associated With *</label>
                    <select name="hod_id" id="reappeal_hod_id" required class="w-full rounded-lg border-gray-300 text-base sm:text-sm focus:border-brand-blue focus:ring-brand-blue">
                        @foreach($hods as $h)
                            <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->department ?: 'HOD' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Job Number</label>
                    <select name="job_number" id="reappeal_job_number" class="w-full rounded-lg border-gray-300 text-base sm:text-sm focus:border-brand-blue focus:ring-brand-blue">
                        <option value="">-- Select Job Number (Optional) --</option>
                        @foreach($jobs as $jobNo)
                            <option value="{{ $jobNo }}">{{ $jobNo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs sm:text-sm font-bold text-gray-800">Expense Line Items *</label>
                    <button type="button" onclick="addReappealItemRow()" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple font-semibold">
                        <i class="fas fa-plus mr-1"></i> Add Line
                    </button>
                </div>
                <div id="reappealItemsContainer" class="space-y-3">
                    <!-- Dynamic Rows -->
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs sm:text-sm font-bold text-gray-800">Add Additional Expenditure Proofs</label>
                    <button type="button" onclick="addProofFileInput('reappealProofContainerStaff')" class="text-xs bg-brand-blue text-white px-3 py-1.5 rounded-md hover:bg-brand-purple transition-all flex items-center font-semibold">
                        <i class="fas fa-plus mr-1"></i> Add File
                    </button>
                </div>
                <div id="reappealProofContainerStaff" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="file" name="proofs[]" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-brand-blue hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1">
                        <button type="button" onclick="if(document.querySelectorAll('#reappealProofContainerStaff > div').length > 1) this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm px-1 flex-shrink-0">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('reappealModal').classList.add('hidden')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 text-sm">
                    Cancel
                </button>
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-brand-pink to-brand-purple text-white font-medium rounded-lg hover:opacity-90 shadow-md text-sm">
                    Resubmit Re-appeal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Settle IOU Modal -->
<div id="settleIouModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-2 sm:p-4 md:p-6 flex items-center justify-center">
    <div class="relative my-auto p-4 sm:p-6 border w-full max-w-2xl shadow-2xl rounded-xl sm:rounded-2xl bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center" id="settleIouModalRef">
                <i class="fas fa-file-signature text-brand-purple mr-2"></i> Settle IOU Request
            </h3>
            <button onclick="document.getElementById('settleIouModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times text-lg sm:text-xl"></i>
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
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center bg-gray-50 p-3 rounded-lg border border-gray-200';
        row.innerHTML = `
            <div class="md:col-span-4">
                <select name="items[${index}][expense_category_id]" required class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
                    <option value="">Select Category *</option>
                    ${catOptions}
                </select>
            </div>
            <div class="md:col-span-3">
                <input type="number" step="0.01" min="0.01" name="items[${index}][amount]" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-4">
                <input type="text" name="items[${index}][description]" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-1 flex justify-end md:justify-center pt-1 md:pt-0">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-red-500 hover:text-red-700 text-xs py-1 flex items-center gap-1 font-semibold">
                    <i class="fas fa-trash"></i> <span class="md:hidden">Remove</span>
                </button>
            </div>
        `;
        container.appendChild(row);
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

    function viewPettyCashDetails(id) {
        fetch(`/petty-cash/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pc = data.pettyCash;
                    document.getElementById('modalRef').innerHTML = `<i class="fas fa-info-circle text-brand-blue mr-2 text-base sm:text-lg"></i> Request: ${pc.reference_number}`;
                    
                    let itemsHtml = pc.items.map(item => `
                        <tr class="border-b border-gray-100 text-xs sm:text-sm">
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

                    let notesHtml = '';
                    if (pc.hod_rejection_note) {
                        notesHtml += `<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800"><strong>HOD Rejection Note:</strong> ${pc.hod_rejection_note}</div>`;
                    }
                    if (pc.admin_rejection_note) {
                        notesHtml += `<div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800 mt-2"><strong>Super Admin Rejection Note:</strong> ${pc.admin_rejection_note}</div>`;
                    }

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

                    document.getElementById('modalBody').innerHTML = `
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 bg-gray-50 p-3.5 sm:p-4 rounded-xl text-xs">
                            <div><span class="text-gray-500 block text-[10px] sm:text-xs">Requested By:</span><strong class="text-gray-800 text-xs sm:text-sm">${pc.user ? pc.user.name : '-'}</strong></div>
                            <div><span class="text-gray-500 block text-[10px] sm:text-xs">Department:</span><strong class="text-gray-800 text-xs sm:text-sm">${pc.department || '-'}</strong></div>
                            <div><span class="text-gray-500 block text-[10px] sm:text-xs">HOD:</span><strong class="text-gray-800 text-xs sm:text-sm">${pc.hod ? pc.hod.name : 'Not Assigned'}</strong></div>
                            <div><span class="text-gray-500 block text-[10px] sm:text-xs">Job Number:</span><strong class="text-gray-800 text-xs sm:text-sm font-mono">${pc.job_number || '-'}</strong></div>
                        </div>

                        ${notesHtml}

                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 mb-2">Expense Line Items</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[400px]">
                                    <thead>
                                        <tr class="bg-gray-100 text-[11px] sm:text-xs font-semibold text-gray-600 uppercase">
                                            <th class="py-2 px-3">Category</th>
                                            <th class="py-2 px-3">Description</th>
                                            <th class="py-2 px-3 text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50 font-bold text-xs sm:text-sm">
                                            <td colspan="2" class="py-2.5 px-3 text-right text-gray-700">Total:</td>
                                            <td class="py-2.5 px-3 text-right text-brand-pink">LKR ${parseFloat(pc.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 mb-2">Proof Attachments</h4>
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
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center bg-gray-50 p-3 rounded-lg border border-gray-200';
        row.innerHTML = `
            <div class="md:col-span-4">
                <select name="items[${index}][expense_category_id]" required class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
                    <option value="">Select Category *</option>
                    ${catOptions}
                </select>
            </div>
            <div class="md:col-span-3">
                <input type="number" step="0.01" min="0.01" name="items[${index}][amount]" value="${amount}" required placeholder="Amount *" class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-4">
                <input type="text" name="items[${index}][description]" value="${desc}" placeholder="Note / Details" class="w-full rounded-md border-gray-300 text-base sm:text-xs focus:ring-brand-blue">
            </div>
            <div class="md:col-span-1 flex justify-end md:justify-center pt-1 md:pt-0">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-red-500 hover:text-red-700 text-xs py-1 flex items-center gap-1 font-semibold">
                    <i class="fas fa-trash"></i> <span class="md:hidden">Remove</span>
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
