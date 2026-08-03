<div class="bg-white rounded-xl shadow-sm mt-8 border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-xl">
        <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Detailed Report
        </h2>
        <div class="flex items-center gap-3">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                    </svg>
                    Columns
                </button>
                <div x-show="open" @click.away="open = false" 
                     class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 max-h-72 overflow-y-auto">
                    <div class="py-1 px-3">
                        @php
                            $columns = [
                                'inv_date' => 'Inv Date',
                                'est_date' => 'Est Date',
                                'close_date' => 'Close Date',
                                'inv_no' => 'Inv No',
                                'est_no' => 'Est No',
                                'job_no' => 'Job No',
                                'month_combined' => 'Invoiced Month/ Closing month',
                                'client' => 'Client Name',
                                'tin' => 'TIN',
                                'currency' => 'Currency',
                                'brand' => 'Brand',
                                'description' => 'Description',
                                'amount' => 'Line Amount',
                                'sscl' => 'SSCL',
                                'vat' => 'VAT',
                                'total' => 'Total Amount',
                                'con_confirmed' => 'Con Confirmed',
                                'category' => 'Revenue Category',
                                'department' => 'Department',
                                'inputter' => 'Data Inputter',
                                'stage' => 'Stages',
                                'advance_status' => 'Advance payment Status',
                                'payment_status' => 'Payment Status',
                                'balance_due' => 'Balance Due'
                            ];
                        @endphp
                        @foreach($columns as $id => $label)
                            <label class="flex items-center py-2 cursor-pointer hover:bg-gray-50 rounded px-2 transition-colors">
                                <input type="checkbox" class="column-toggle rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition-all duration-200" 
                                    data-column="{{ $id }}" checked>
                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ route('reports.export', array_merge(request()->all(), ['type' => 'detailed'])) }}" id="export-csv-btn"
               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 no-wrap-table">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $id => $label)
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap {{ str_contains($id, 'amount') || in_array($id, ['sscl', 'vat', 'total', 'con_confirmed', 'balance_due']) ? 'text-right' : '' }}" data-col="{{ $id }}">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($detailedData as $deal)
                    @php
                        $item = $deal->first_invoice_item ?? null;
                        $invoice = $item->invoice ?? null; // Could be a mock object from controller
                        // Access estimate from deal directly or from invoice
                        $estimate = $deal->estimates->first() ?? null;
                        
                        $total = $invoice->total_amount ?? ($deal->revenue ?? 0);
                        $balanceDue = ($invoice && ($invoice->status ?? '') === 'paid') ? 0 : $total;
                        $advanceStatus = ($estimate && ($estimate->advance_received_amount ?? 0) > 0) ? 'RECEIVED' : 'PENDING';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="inv_date">{{ $invoice->date ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="est_date">{{ $estimate->date ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-bold text-indigo-600" data-col="close_date">{{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-col="inv_no">{{ $invoice->invoice_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-col="est_no">{{ $estimate->reference_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono" data-col="job_no">{{ $deal->job_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="month_combined">{{ ($invoice && isset($invoice->date) && $invoice->date !== 'N/A') ? date('M Y', strtotime($invoice->date)) : ($deal->close_date ? date('M Y', strtotime($deal->close_date)) : 'N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" data-col="client">{{ $deal->customer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="tin">{{ $deal->customer->customer_tax_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700" data-col="currency">{{ $deal->currency ?? 'LKR' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="brand">{{ $estimate->brand_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600" data-col="description">{{ $item->description ?? $deal->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900" data-col="amount">{{ number_format($item->amount ?? $deal->revenue ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600" data-col="sscl">{{ number_format($item->sscl_amount ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600" data-col="vat">{{ number_format($item->vat_amount ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-indigo-600" data-col="total">{{ number_format($item->total_with_vat ?? $deal->revenue ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600" data-col="con_confirmed">{{ number_format($deal->contribution ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="category">{{ $item->revenue_category ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="department">{{ $item->department ?? ($deal->owner->department ?? 'N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="inputter">{{ $deal->owner->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-col="stage">{{ $deal->stage ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap" data-col="advance_status">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $advanceStatus === 'RECEIVED' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $advanceStatus }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" data-col="payment_status">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $invoice && ($invoice->status ?? '') === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ strtoupper($invoice->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600" data-col="balance_due">{{ number_format($balanceDue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="20" class="px-6 py-10 text-center text-sm text-gray-500 italic bg-gray-50/30">
                            No detailed report data found for the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($detailedData->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $detailedData->links() }}
        </div>
    @endif
</div>

<style>
    .no-wrap-table th, .no-wrap-table td {
        white-space: nowrap;
        min-width: 120px;
    }
    .no-wrap-table td[data-col="description"] {
        min-width: 250px;
        white-space: normal;
    }
    .no-wrap-table th[data-col="inv_date"], .no-wrap-table td[data-col="inv_date"] {
        min-width: 100px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.column-toggle');
        const exportBtn = document.getElementById('export-csv-btn');

        // Load from localStorage or use default (all checked)
        let savedCols = localStorage.getItem('detailed_report_columns');
        if (savedCols) {
            try {
                savedCols = JSON.parse(savedCols);
                toggles.forEach(toggle => {
                    const colId = toggle.getAttribute('data-column');
                    toggle.checked = savedCols.includes(colId);
                });
            } catch (e) {
                console.error(e);
            }
        }

        const updateExportUrl = () => {
            if (!exportBtn) return;
            const checkedCols = [];
            toggles.forEach(toggle => {
                if (toggle.checked) {
                    checkedCols.push(toggle.getAttribute('data-column'));
                }
            });

            // Save to localStorage
            localStorage.setItem('detailed_report_columns', JSON.stringify(checkedCols));

            // Get current URL
            let baseUrl = exportBtn.getAttribute('data-base-url');
            if (!baseUrl) {
                baseUrl = exportBtn.href;
                exportBtn.setAttribute('data-base-url', baseUrl);
            }
            const url = new URL(baseUrl);
            url.searchParams.set('columns', checkedCols.join(','));
            exportBtn.href = url.toString();
        };

        const updateVisibility = () => {
            toggles.forEach(toggle => {
                const colId = toggle.getAttribute('data-column');
                const cells = document.querySelectorAll(`[data-col="${colId}"]`);
                cells.forEach(cell => {
                    cell.style.display = toggle.checked ? '' : 'none';
                });
            });
            updateExportUrl();
        };

        toggles.forEach(toggle => {
            toggle.addEventListener('change', updateVisibility);
        });

        // Initialize display
        updateVisibility();
    });
</script>
