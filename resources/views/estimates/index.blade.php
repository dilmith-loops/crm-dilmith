@extends('layouts.app')

@section('header', 'Estimates')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden" x-data="{ 
        columns: $persist(['reference', 'customer', 'brand', 'date', 'amount', 'status', 'actions']).as('estimates_columns'),
        showPicker: false,
        showAttachments: false,
        activeAttachments: [],
        activePoFile: null,
        activeRef: '',
        isColumnVisible(col) { return this.columns.includes(col); },
        toggleColumn(col) {
            if (this.isColumnVisible(col)) {
                this.columns = this.columns.filter(c => c !== col);
            } else {
                this.columns.push(col);
            }
        },
        openAttachments(attachments, poFile, ref) {
            this.activeAttachments = attachments;
            this.activePoFile = poFile;
            this.activeRef = ref;
            this.showAttachments = true;
        }
    }" x-init="if (!columns.includes('heading')) { columns.splice(columns.indexOf('brand') + 1, 0, 'heading'); }">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">Manage Estimates</h3>
            {{-- Columns and Create Estimate buttons hidden --}}
        </div>


        <!-- Search and Filters -->
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ route('estimates.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 {{ auth()->user()->role === 'HOD' ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-3 sm:gap-4 items-end">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Reference or Customer"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue text-base sm:text-sm h-10 px-3 border">
                </div>
                @if(auth()->user()->role === 'HOD')
                <div>
                    <label for="manager_id" class="block text-xs font-medium text-gray-500 uppercase mb-1">Manager</label>
                    <select name="manager_id" id="manager_id" 
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue text-base sm:text-sm h-10 px-3 border">
                        <option value="">All Managers</option>
                        @foreach($managers as $id => $name)
                            <option value="{{ $id }}" {{ request('manager_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label for="from_date" class="block text-xs font-medium text-gray-500 uppercase mb-1">From Date</label>
                    <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue text-base sm:text-sm h-10 px-3 border">
                </div>
                <div>
                    <label for="to_date" class="block text-xs font-medium text-gray-500 uppercase mb-1">To Date</label>
                    <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue text-base sm:text-sm h-10 px-3 border">
                </div>
                <div class="flex space-x-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-colors h-10">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('estimates.index') }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition-colors h-10 flex items-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>


        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th x-show="isColumnVisible('reference')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference
                        </th>
                        <th x-show="isColumnVisible('customer')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                        </th>
                        <th x-show="isColumnVisible('brand')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand
                        </th>
                        <th x-show="isColumnVisible('heading')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Heading
                        </th>
                        <th x-show="isColumnVisible('date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th x-show="isColumnVisible('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                        <th x-show="isColumnVisible('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th x-show="isColumnVisible('actions')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($estimates as $estimate)
                        @php
                            $user = auth()->user();
                            $isRestricted = !in_array($user->role, ['Super Admin', 'Management']);
                        @endphp
                        <tr>
                            <td x-show="isColumnVisible('reference')" class="px-6 py-4 white-space-nowrap text-sm font-medium text-gray-900">
                                {{ $estimate->reference_number }}
                            </td>
                            <td x-show="isColumnVisible('customer')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $estimate->customer->name }}</td>
                            <td x-show="isColumnVisible('brand')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">
                                @if($estimate->brand_name)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $estimate->brand_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">No Brand</span>
                                @endif
                            </td>
                            <td x-show="isColumnVisible('heading')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">
                                {{ $estimate->heading ?: 'N/A' }}
                            </td>
                            <td x-show="isColumnVisible('date')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $estimate->date }}</td>
                            <td x-show="isColumnVisible('amount')" class="px-6 py-4 white-space-nowrap text-sm text-gray-900 font-bold">
                                {{ $estimate->currency ?? 'LKR' }} {{ number_format($estimate->total_amount, 2) }}</td>
                            <td x-show="isColumnVisible('status')" class="px-6 py-4 white-space-nowrap">
                                @if(auth()->user()->role !== 'Super Admin' && (!$estimate->canEdit($user) || ($isRestricted && !in_array($estimate->status, ['draft', 'approved'])) || $estimate->status == 'invoiced'))
                                    <span class="text-xs font-semibold rounded-full px-2 py-1 inline-block
                                                                                        @if($estimate->status == 'draft') bg-gray-100 text-gray-800
                                                                                        @elseif($estimate->status == 'approved') bg-yellow-100 text-yellow-800
                                                                                        @elseif($estimate->status == 'accepted' || $estimate->status == 'ready_to_invoice') bg-green-100 text-green-800
                                                                                        @elseif($estimate->status == 'rejected') bg-red-100 text-red-800
                                                                                        @elseif($estimate->status == 'invoiced') bg-blue-100 text-blue-800
                                                                                        @endif">
                                        {{ $estimate->status == 'draft' ? 'Pending' : ($estimate->status == 'ready_to_invoice' ? 'Ready to Invoice' : ucfirst($estimate->status == 'accepted' ? 'Ready to Invoice (Old)' : $estimate->status)) }}
                                    </span>
                                @else
                                    <form action="{{ route('estimates.updateStatus', $estimate) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-full px-2 py-1 border-none focus:ring-0 cursor-pointer w-full
                                                                                                    @if($estimate->status == 'draft') bg-gray-100 text-gray-800
                                                                                                    @elseif($estimate->status == 'approved') bg-yellow-100 text-yellow-800
                                                                                                    @elseif($estimate->status == 'accepted' || $estimate->status == 'ready_to_invoice') bg-green-100 text-green-800
                                                                                                    @elseif($estimate->status == 'rejected') bg-red-100 text-red-800
                                                                                                    @elseif($estimate->status == 'invoiced') bg-blue-100 text-blue-800
                                                                                                    @endif">
                                            @if($isRestricted)
                                                <!-- Restricted User Options (Draft -> Ready to Invoice, or Approved -> Ready to Invoice) -->
                                                @if($estimate->status == 'approved')
                                                    <option value="approved" selected>Approved</option>
                                                    <option value="ready_to_invoice">Ready to Invoice</option>
                                                @else
                                                    <option value="draft" {{ $estimate->status == 'draft' ? 'selected' : '' }}>Pending</option>
                                                    <option value="ready_to_invoice">Ready to Invoice</option>
                                                @endif
                                            @else
                                                <!-- Admin Options -->
                                                <option value="draft" {{ $estimate->status == 'draft' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ $estimate->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="rejected" {{ $estimate->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                <option value="ready_to_invoice" {{ $estimate->status == 'ready_to_invoice' ? 'selected' : '' }}>Ready to Invoice</option>
                                                @if(auth()->user()->role === 'Super Admin')
                                                    <option value="invoiced" {{ $estimate->status == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                                                @endif
                                            @endif
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td x-show="isColumnVisible('actions')"
                                class="px-6 py-4 white-space-nowrap text-right text-sm font-medium flex justify-end gap-2 items-center">
                                @if($estimate->thirdPartyCosts->count() > 0 || $estimate->po_file_path)
                                    <button @click="openAttachments({{ json_encode($estimate->thirdPartyCosts) }}, '{{ $estimate->po_file_path }}', '{{ $estimate->reference_number }}')"
                                        class="text-brand-purple hover:text-brand-blue relative" title="View Attached Documents">
                                        <i class="fas fa-paperclip"></i>
                                        @php
                                            $totalDocs = $estimate->thirdPartyCosts->whereNotNull('file_path')->count() + ($estimate->po_file_path ? 1 : 0);
                                        @endphp
                                        @if($totalDocs > 1)
                                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1 rounded-full border border-white">
                                                {{ $totalDocs }}
                                            </span>
                                        @endif
                                    </button>
                                @endif

                                <!-- View -->
                                <a href="{{ route('estimates.show', $estimate) }}" target="_blank"
                                    class="text-brand-blue hover:text-brand-purple" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @php
                                    $canEditOrDelete = false;
                                    if ($user->role === 'Super Admin') {
                                        $canEditOrDelete = true;
                                    } elseif ($user->role === 'Management') {
                                        $canEditOrDelete = !in_array($estimate->status, ['invoiced', 'approved']);
                                    } else {
                                        $canEditOrDelete = $estimate->status === 'draft';
                                    }
                                @endphp

                                <!-- Edit -->
                                @if($canEditOrDelete)
                                    <a href="{{ route('estimates.edit', $estimate) }}" class="text-gray-600 hover:text-brand-blue"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                <!-- Delete -->
                                @if($canEditOrDelete)
                                    <form action="{{ route('estimates.destroy', $estimate) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this estimate?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">No estimates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Attachments Modal -->
        <div x-show="showAttachments" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showAttachments" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100" 
                    x-transition:leave-end="opacity-0" 
                    class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showAttachments" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    @click.away="showAttachments = false">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-brand-purple bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-file-invoice text-brand-purple"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
                                    Attached Documents - <span x-text="activeRef"></span>
                                </h3>
                                <div class="mt-4 space-y-6">
                                    <!-- PO Section -->
                                    <div x-show="activePoFile">
                                        <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Purchase Order Document</h5>
                                        <div class="flex items-center justify-between p-3 bg-blue-50 bg-opacity-50 rounded-lg border border-blue-100">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center bg-white rounded border border-blue-100 mr-3">
                                                    <i class="fas fa-file-contract text-blue-500"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-700 leading-tight">PO Document</p>
                                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Estimate Attachment</p>
                                                </div>
                                            </div>
                                            <a :href="'/' + activePoFile" target="_blank"
                                                class="px-4 py-2 bg-brand-blue hover:bg-brand-purple text-white text-xs font-bold rounded shadow-sm transition-all focus:outline-none flex items-center">
                                                <i class="fas fa-external-link-alt mr-2"></i> VIEW
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Third Party Section -->
                                    <div x-show="activeAttachments.length > 0">
                                        <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Third Party Cost Documents</h5>
                                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                            <template x-for="cost in activeAttachments" :key="cost.id">
                                                <div x-show="cost.file_path" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 mb-2">
                                                    <div class="flex items-center">
                                                        <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center bg-white rounded border border-gray-100 mr-3">
                                                            <i class="fas fa-file-pdf text-red-500"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-700 leading-tight" x-text="cost.supplier"></p>
                                                            <p class="text-[10px] text-gray-500 uppercase tracking-wider" x-text="cost.department || 'General'"></p>
                                                        </div>
                                                    </div>
                                                    <a :href="'/uploads/' + cost.file_path" target="_blank"
                                                        class="px-4 py-2 bg-brand-blue hover:bg-brand-purple text-white text-xs font-bold rounded shadow-sm transition-all focus:outline-none flex items-center">
                                                        <i class="fas fa-external-link-alt mr-2"></i> VIEW
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showAttachments = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection