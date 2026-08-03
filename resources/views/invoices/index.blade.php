@extends('layouts.app')

@section('header', 'Invoices')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden" x-data="{ 
        columns: $persist(['invoice_no', 'customer', 'date', 'due_date', 'amount', 'status', 'actions']).as('invoices_columns'),
        showPicker: false,
        showAttachments: false,
        activeAttachments: [],
        activeRef: '',
        isColumnVisible(col) { return this.columns.includes(col); },
        toggleColumn(col) {
            if (this.isColumnVisible(col)) {
                this.columns = this.columns.filter(c => c !== col);
            } else {
                this.columns.push(col);
            }
        },
        openAttachments(attachments, ref) {
            this.activeAttachments = attachments;
            this.activeRef = ref;
            this.showAttachments = true;
        }
    }" x-init="if (!columns.includes('heading')) { columns.splice(columns.indexOf('customer') + 1, 0, 'heading'); }">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">All Invoices</h3>
            <div class="relative" @click.away="showPicker = false">
                <button @click="showPicker = !showPicker" 
                    class="px-3.5 py-1.5 sm:px-4 sm:py-2 bg-white border border-gray-300 text-gray-700 text-xs sm:text-sm font-medium rounded-md hover:bg-gray-50 transition-colors flex items-center shadow-sm">
                    <i class="fas fa-columns mr-2"></i>Columns
                </button>
                <div x-show="showPicker" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl z-30 overflow-hidden"
                    style="display: none;">
                    <div class="p-4">
                        <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Visible Columns</h5>
                        <div class="space-y-3">
                            @foreach([
                                'invoice_no' => 'Invoice #',
                                'customer' => 'Customer',
                                'heading' => 'Project Heading',
                                'date' => 'Date',
                                'due_date' => 'Due Date',
                                'amount' => 'Amount',
                                'status' => 'Status',
                                'actions' => 'Actions'
                            ] as $key => $label)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" :checked="isColumnVisible('{{ $key }}')" @change="toggleColumn('{{ $key }}')"
                                        class="w-4 h-4 text-brand-blue border-gray-200 rounded focus:ring-brand-blue transition-colors">
                                    <span class="ml-3 text-xs font-bold text-slate-600 group-hover:text-brand-blue transition-colors">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="bg-gray-50 px-4 sm:px-6 py-2.5 border-b border-gray-200 flex overflow-x-auto whitespace-nowrap space-x-2 sm:space-x-4 text-xs sm:text-sm">
            <a href="{{ route('invoices.ready') }}"
                class="px-3 py-1 rounded-md {{ request()->routeIs('invoices.ready') ? 'bg-green-100 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-200' }}">Ready
                to Invoice</a>
            <a href="{{ route('invoices.index') }}"
                class="px-3 py-1 rounded-md {{ request()->routeIs('invoices.index') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-200' }}">Invoices</a>
            @if(auth()->user()->role === 'Super Admin')
                <a href="{{ route('invoices.proforma') }}"
                    class="px-3 py-1 rounded-md {{ request()->routeIs('invoices.proforma') ? 'bg-purple-100 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-200' }}">Proforma
                    Invoices</a>
            @endif
            <a href="{{ route('invoices.invoiced') }}"
                class="px-3 py-1 rounded-md {{ request()->routeIs('invoices.invoiced') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-200' }}">Invoiced
                Estimates</a>
            <a href="{{ route('invoices.rejected') }}"
                class="px-3 py-1 rounded-md {{ request()->routeIs('invoices.rejected') ? 'bg-red-100 text-red-700 font-semibold' : 'text-gray-600 hover:bg-gray-200' }}">Rejected
                Invoices</a>
        </div>

        <!-- Search and Filters -->
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 {{ auth()->user()->role === 'HOD' ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-3 sm:gap-4 items-end">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Invoice # or Customer"
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
                    <a href="{{ url()->current() }}"
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
                        <th x-show="isColumnVisible('invoice_no')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #
                        </th>
                        <th x-show="isColumnVisible('customer')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                        </th>
                        <th x-show="isColumnVisible('heading')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Heading
                        </th>
                        <th x-show="isColumnVisible('date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th x-show="isColumnVisible('due_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date
                        </th>
                        <th x-show="isColumnVisible('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                        <th x-show="isColumnVisible('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th x-show="isColumnVisible('actions')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td x-show="isColumnVisible('invoice_no')" class="px-6 py-4 white-space-nowrap text-sm font-medium text-gray-900">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td x-show="isColumnVisible('customer')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $invoice->customer->name }}</td>
                            <td x-show="isColumnVisible('heading')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">
                                {{ $invoice->heading ?: ($invoice->estimate->heading ?? 'N/A') }}
                            </td>
                            <td x-show="isColumnVisible('date')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $invoice->date ?: ($invoice->estimate->date ?? '') }}</td>
                            <td x-show="isColumnVisible('due_date')" class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $invoice->due_date ?: ($invoice->estimate ? \Carbon\Carbon::parse($invoice->estimate->date)->addMonth()->toDateString() : '') }}</td>
                            <td x-show="isColumnVisible('amount')" class="px-6 py-4 white-space-nowrap text-sm text-gray-900 font-bold">
                                {{ $invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR')) }} {{ number_format($invoice->total_amount, 2) }}</td>
                            <td x-show="isColumnVisible('status')" class="px-6 py-4 white-space-nowrap">
                                @if(auth()->user()->role === 'Super Admin')
                                    <form action="{{ route('invoices.updateStatus', $invoice) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()"
                                            class="text-xs font-semibold rounded-full px-2 py-1 border-none focus:ring-0 cursor-pointer
                                                                                                                                                                             @if($invoice->status == 'unpaid') bg-yellow-100 text-yellow-800
                                                                                                                                                                             @elseif($invoice->status == 'paid') bg-green-100 text-green-800
                                                                                                                                                                             @elseif($invoice->status == 'overdue') bg-red-100 text-red-800
                                                                                                                                                                             @endif">
                                            <option value="unpaid" {{ $invoice->status == 'unpaid' ? 'selected' : '' }}>Unpaid
                                            </option>
                                            <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="overdue" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>Overdue
                                            </option>
                                        </select>
                                    </form>
                                @else
                                    <span class="text-xs font-semibold rounded-full px-2 py-1
                                                                                                    @if($invoice->status == 'unpaid') bg-yellow-100 text-yellow-800
                                                                                                    @elseif($invoice->status == 'paid') bg-green-100 text-green-800
                                                                                                    @elseif($invoice->status == 'overdue') bg-red-100 text-red-800
                                                                                                    @endif">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                @endif
                            </td>
                            <td x-show="isColumnVisible('actions')" class="px-6 py-4 white-space-nowrap text-right text-sm font-medium flex justify-end space-x-2 items-center">
                                @if($invoice->estimate && $invoice->estimate->thirdPartyCosts->count() > 0)
                                    <button @click="openAttachments({{ json_encode($invoice->estimate->thirdPartyCosts) }}, '{{ $invoice->estimate->reference_number }}')"
                                        class="text-brand-purple hover:text-brand-blue relative" title="View Third Party Documents">
                                        <i class="fas fa-paperclip"></i>
                                        @if($invoice->estimate->thirdPartyCosts->count() > 1)
                                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1 rounded-full border border-white">
                                                {{ $invoice->estimate->thirdPartyCosts->count() }}
                                            </span>
                                        @endif
                                    </button>
                                @endif
                                <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-brand-blue hover:text-brand-purple"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->role === 'Super Admin')
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-gray-600 hover:text-brand-blue" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if(auth()->user()->role === 'Super Admin')
                                    <form action="{{ route('invoices.duplicate', $invoice) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-brand-purple hover:text-brand-pink"
                                            title="Duplicate to Estimate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Attachments Modal -->
        <div x-show="showAttachments" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showAttachments = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-paperclip mr-2 text-brand-purple"></i>
                            Attachments for <span x-text="activeRef" class="ml-1 text-brand-blue"></span>
                        </h3>
                        <button @click="showAttachments = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <template x-if="activeAttachments.length > 0">
                            <div class="space-y-3">
                                <template x-for="cost in activeAttachments" :key="cost.id">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-brand-purple transition-all group">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full bg-brand-purple bg-opacity-10 flex items-center justify-center text-brand-purple">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900" x-text="cost.supplier"></p>
                                                <p class="text-[10px] text-gray-500 uppercase tracking-wider" x-text="cost.department || 'General'"></p>
                                            </div>
                                        </div>
                                        <a :href="'/uploads/' + cost.file_path" 
                                           target="_blank" 
                                           class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-brand-purple transition-all shadow-sm">
                                            <i class="fas fa-external-link-alt mr-1.5"></i>
                                            VIEW
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="activeAttachments.length === 0">
                            <div class="text-center py-8">
                                <i class="fas fa-file-invoice text-gray-200 text-5xl mb-3"></i>
                                <p class="text-gray-500">No attachments found for this estimate.</p>
                            </div>
                        </template>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                        <button @click="showAttachments = false" 
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-all shadow-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection