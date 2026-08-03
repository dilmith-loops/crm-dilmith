@extends('layouts.app')

@section('header', 'Ready to Invoice')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden" x-data="{ 
        showAttachments: false,
        activeAttachments: [],
        activePoFile: null,
        activeRef: '',
        openAttachments(attachments, poFile, ref) {
            this.activeAttachments = attachments;
            this.activePoFile = poFile;
            this.activeRef = ref;
            this.showAttachments = true;
        }
    }">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700">Estimates Ready to Invoice</h3>
        </div>

        <!-- TABS -->
        <div class="bg-gray-50 px-6 py-2 border-b border-gray-200 flex space-x-4">
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
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-1 {{ auth()->user()->role === 'HOD' ? 'md:grid-cols-5' : 'md:grid-cols-4' }} gap-4 items-end">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Estimate # or Customer"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm h-10 px-3 border">
                </div>
                @if(auth()->user()->role === 'HOD')
                <div>
                    <label for="manager_id" class="block text-xs font-medium text-gray-500 uppercase mb-1">Manager</label>
                    <select name="manager_id" id="manager_id" 
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm h-10 px-3 border">
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
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm h-10 px-3 border">
                </div>
                <div>
                    <label for="to_date" class="block text-xs font-medium text-gray-500 uppercase mb-1">To Date</label>
                    <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm h-10 px-3 border">
                </div>
                <div class="flex space-x-2">
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

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Heading
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($estimates as $estimate)
                        <tr>
                            <td class="px-6 py-4 white-space-nowrap text-sm font-medium text-gray-900">
                                {{ $estimate->temp_invoice_number }}
                            </td>
                            <td class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $estimate->customer->name }}</td>
                            <td class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $estimate->estimate->heading ?? 'N/A' }}</td>
                            <td class="px-6 py-4 white-space-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="fas fa-user-circle mr-1"></i> {{ $estimate->estimate->user->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 white-space-nowrap text-sm text-gray-500">{{ $estimate->date }}</td>
                            <td class="px-6 py-4 white-space-nowrap text-sm text-gray-900 font-bold">
                                {{ $estimate->estimate->currency ?? 'LKR' }} {{ number_format($estimate->total_amount, 2) }}</td>
                            <td class="px-6 py-4 white-space-nowrap text-right text-sm font-medium flex justify-end space-x-2 items-center">
                                @if(($estimate->estimate->thirdPartyCosts ?? collect())->count() > 0 || ($estimate->estimate->po_file_path ?? null))
                                    <button @click="openAttachments({{ json_encode($estimate->estimate->thirdPartyCosts ?? []) }}, '{{ $estimate->estimate->po_file_path ?? '' }}', '{{ $estimate->temp_invoice_number }}')"
                                        class="text-brand-purple hover:text-brand-blue relative" title="View Attached Documents">
                                        <i class="fas fa-paperclip"></i>
                                        @php
                                            $totalDocs = ($estimate->estimate->thirdPartyCosts ?? collect())->whereNotNull('file_path')->count() + ($estimate->estimate->po_file_path ? 1 : 0);
                                        @endphp
                                        @if($totalDocs > 1)
                                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1 rounded-full border border-white">
                                                {{ $totalDocs }}
                                            </span>
                                        @endif
                                    </button>
                                @endif
                                @if($estimate->estimate)
                                    <a href="{{ route('estimates.show', $estimate->estimate->id) }}" target="_blank"
                                        class="text-brand-blue hover:text-brand-purple mr-3" title="View Estimate">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif

                                @if($estimate->estimate && $estimate->estimate->canEdit())
                                    <button onclick="confirmRevert('{{ route('temp-invoices.revert', $estimate->id) }}', '{{ $estimate->temp_invoice_number }}')"
                                        class="px-3 py-1 bg-amber-500 text-white rounded hover:bg-amber-600 text-xs shadow-sm font-semibold inline-flex items-center mr-1"
                                        title="Revert to Pending">
                                        <i class="fas fa-undo mr-1"></i> Revert
                                    </button>
                                @endif

                                @if(in_array(auth()->user()->role, ['Super Admin', 'Management']))
                                    <a href="{{ route('temp-invoices.edit', $estimate->id) }}"
                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs shadow-sm font-semibold inline-flex items-center">
                                        <i class="fas fa-cog mr-1"></i> Process Invoice
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">No temporary invoices found.
                            </td>
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
                    
                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-6">
                        <!-- PO Section -->
                        <div x-show="activePoFile">
                            <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Purchase Order Document</h5>
                            <div class="flex items-center justify-between p-3 bg-blue-50 bg-opacity-50 rounded-lg border border-blue-100 group">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-500 bg-opacity-10 flex items-center justify-center text-blue-500">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">PO Document</p>
                                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">Estimate Attachment</p>
                                    </div>
                                </div>
                                <a :href="'/' + activePoFile" 
                                   target="_blank" 
                                   class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-brand-purple transition-all shadow-sm">
                                    <i class="fas fa-external-link-alt mr-1.5"></i>
                                    VIEW
                                </a>
                            </div>
                        </div>

                        <!-- Third Party Section -->
                        <div x-show="activeAttachments.length > 0">
                            <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Third Party Cost Documents</h5>
                            <div class="space-y-3">
                                <template x-for="cost in activeAttachments" :key="cost.id">
                                    <div x-show="cost.file_path" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-brand-purple transition-all group">
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
                        </div>
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

@push('scripts')
<script>
    function confirmRevert(url, ref) {
        Swal.fire({
            title: 'Revert to Pending State?',
            text: `Are you sure you want to revert ${ref} back to pending state? This will delete the temporary invoice and change the estimate status to Pending`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, revert it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit a form dynamically
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush