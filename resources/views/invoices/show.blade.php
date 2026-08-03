@extends('layouts.app')

@section('no_sidebar')
@endsection

@push('head')
<style>
    /* Force base font size for all text elements inside the invoice container */
    #invoice-container, 
    #invoice-container div, 
    #invoice-container span, 
    #invoice-container p,
    #paginated-invoice-view,
    #paginated-invoice-view div,
    #paginated-invoice-view span,
    #paginated-invoice-view p,
    #measure-container,
    #measure-container div,
    #measure-container span,
    #measure-container p {
        font-size: 13px;
    }
    /* Preserve large title size */
    #invoice-container .invoice-header div,
    #paginated-invoice-view .invoice-header div,
    #measure-container .invoice-header div {
        font-size: 17px;
    }
    /* Preserve footer text size */
    #invoice-container .invoice-footer,
    #paginated-invoice-view .invoice-footer,
    #measure-container .invoice-footer {
        font-size: 12px;
    }

    .quill-content h1 {
        font-size: 1.125rem; /* text-lg */
        font-weight: 700; /* font-bold */
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        margin-top: 0.5rem;
    }
    .quill-content h2 {
        font-size: 1rem; /* text-base */
        font-weight: 600; /* font-semibold */
        font-style: italic;
        margin-bottom: 0.25rem;
        margin-top: 0.5rem;
    }
    .quill-content ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-top: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .quill-content p {
        margin-bottom: 0.25rem;
    }
    /* Reset margins for first and last children to keep table cells neat */
    .quill-content > *:first-child { margin-top: 0; }
    .quill-content > *:last-child { margin-bottom: 0; }
</style>
@endpush

@section('header')
    <div class="flex justify-between items-center no-print">
        <span>Invoice Details</span>
        <div>
            <a href="{{ route('invoices.index') }}" class="bg-brand-blue text-white px-4 py-2 rounded-md hover:bg-brand-purple text-sm mr-2 inline-flex items-center">
                <i class="fas fa-arrow-left mr-1.5"></i> Back
            </a>
            <button id="download-btn" onclick="downloadPDF()" class="bg-slate-700 text-white px-4 py-2 rounded-md hover:bg-slate-800 text-sm mr-2 inline-flex items-center">
                <i class="fas fa-download mr-1.5"></i> Download as PDF
            </button>
            <form action="{{ route('invoices.duplicate', $invoice) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-brand-purple text-white px-4 py-2 rounded-md hover:bg-brand-pink text-sm inline-flex items-center">
                    <i class="fas fa-copy mr-1.5"></i> Duplicate to Estimate
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')

    <!-- MAIN BORDERED CONTAINER -->
    <div class="max-w-4xl mx-auto bg-white border border-black p-4 md:p-[24px] print:border print:m-4 text-black font-sans text-[13px] leading-tight mb-8" id="invoice-container">
        
        <!-- Header with Logo for Proforma -->
        <div class="flex justify-between items-center mb-[20px] mt-2 invoice-header">
            <div class="w-1/3">
                <img src="{{ asset('images/logo_loops.png') }}" alt="Loops Logo" class="h-12 w-auto">
            </div>
            <div class="w-1/3 flex justify-center">
                <div class="border border-black px-12 py-2 bg-white font-bold text-[17px] uppercase tracking-wide whitespace-nowrap">
                    {{ $invoice->is_proforma ? 'PROFORMA INVOICE' : (($invoice->invoice_type ?: ($invoice->estimate->invoice_type ?? 'tax_invoice')) === 'invoice' ? 'INVOICE' : 'TAX INVOICE') }}
                </div>
            </div>
            <div class="w-1/3"></div>
        </div>

        <!-- THE MAIN GRID USING EXPLICIT BORDERS FOR BULLETPROOF PRINTING -->
        <div class="border-t border-black invoice-grid-wrapper">
            <!-- Top Section: 2 Columns -->
            <div class="flex invoice-date-section">
                <div class="w-1/2 p-3 border-l border-r border-b border-black">
                    <span class="font-bold text-[13px]">Date of Invoice:</span> <span class="text-[13px]">{{ \Carbon\Carbon::parse($invoice->date ?: ($invoice->estimate->date ?? now()))->format('d-m-Y') }}</span>
                </div>
                <div class="w-1/2 p-3 border-r border-b border-black">
                    <span class="font-bold text-[13px]">{{ $invoice->is_proforma ? 'Proforma Invoice No.:' : (($invoice->invoice_type ?: ($invoice->estimate->invoice_type ?? 'tax_invoice')) === 'invoice' ? 'Invoice No.:' : 'Tax Invoice No.:') }}</span> <span class="text-[13px]">{{ $invoice->invoice_number }}</span>
                </div>
            </div>

            <!-- Supplier & Purchaser Section -->
            <div class="flex invoice-supplier-section">
                <div class="w-1/2 p-3 border-l border-r border-b border-black min-h-[140px] flex flex-col justify-between">
                    <div class="space-y-1">
                        <div><span class="font-bold text-[13px]">Supplier's TIN:</span> <span class="text-[13px]">{{ \App\Models\Setting::get('company_vat') }}</span></div>
                        <div><span class="font-bold text-[13px]">Supplier's Name:</span> <span class="text-[13px]">{{ \App\Models\Setting::get('company_name') }}</span></div>
                        <div class="flex"><span class="font-bold text-[13px] whitespace-nowrap mr-1">Address:</span> <span class="text-[13px]">{{ \App\Models\Setting::get('company_address_1') }} {{ \App\Models\Setting::get('company_address_2') }}</span></div>
                    </div>
                    <div class="mt-3"><span class="font-bold text-[13px]">Telephone No:</span> <span class="text-[13px]">{{ \App\Models\Setting::get('company_phone') }}</span></div>
                </div>
                <div class="w-1/2 p-3 border-r border-b border-black min-h-[140px] flex flex-col justify-between">
                    <div class="space-y-1">
                        <div><span class="font-bold text-[13px]">Purchaser's TIN:</span> <span class="text-[13px]">{{ $invoice->customer->customer_vat_registration_number ?? 'N/A' }}</span></div>
                        <div><span class="font-bold text-[13px]">Purchaser's Name:</span> <span class="text-[13px]">{{ $invoice->customer->name }}</span></div>
                        <div class="flex"><span class="font-bold text-[13px] whitespace-nowrap mr-1">Address:</span> <span class="text-[13px]">{{ $invoice->customer->billing_address ?: $invoice->customer->address }}</span></div>
                    </div>
                    <div class="mt-3"><span class="font-bold text-[13px]">Telephone No:</span> <span class="text-[13px]">{{ $invoice->customer->telephone ?: $invoice->customer->phone }}</span></div>
                </div>
            </div>

            <!-- Delivery & Supply Section -->
            <div class="flex invoice-delivery-section">
                <div class="w-1/2 p-3 border-l border-r border-b border-black">
                    <span class="font-bold text-[13px]">Date of Delivery:</span> <span class="text-[13px]">{{ $invoice->date_of_delivery ? \Carbon\Carbon::parse($invoice->date_of_delivery)->format('d-m-Y') : ($invoice->estimate && $invoice->estimate->date_of_delivery ? \Carbon\Carbon::parse($invoice->estimate->date_of_delivery)->format('d-m-Y') : 'N/A') }}</span>
                </div>
                <div class="w-1/2 p-3 border-r border-b border-black">
                    <span class="font-bold text-[13px]">Place of Supply:</span> <span class="text-[13px]">{{ $invoice->place_of_supply ?: ($invoice->estimate->place_of_supply ?? 'N/A') }}</span>
                </div>
            </div>

            <!-- Additional Info Section -->
            <div class="p-3 border-l border-r border-b border-black min-h-[60px] invoice-info-section">
                <div class="font-bold mb-1 text-[13px]">Additional Information if any:</div>
                <div class="text-[13px]">
                    @if($invoice->additional_information)
                        {{ $invoice->additional_information }}
                    @elseif($invoice->estimate && $invoice->estimate->additional_notes)
                        {{ $invoice->estimate->additional_notes }}
                    @elseif($invoice->heading)
                        {{ $invoice->heading }}
                    @elseif($invoice->estimate && $invoice->estimate->heading)
                        {{ $invoice->estimate->heading }}
                    @else
                        N/A
                    @endif
                </div>
            </div>

            <!-- Items Table Headers -->
            <div class="flex font-bold text-[13px] text-center invoice-table-header">
                <div class="p-2 w-[8%] border-l border-r border-b border-black">Ref</div>
                <div class="p-2 w-[42%] border-r border-b border-black text-left pl-4">Description of Goods or Services</div>
                <div class="p-2 w-[12%] border-r border-b border-black">Quantity</div>
                <div class="p-2 w-[18%] border-r border-b border-black">Unit Price</div>
                <div class="p-2 w-[20%] border-r border-b border-black leading-tight flex items-center justify-center">Amount Excluding<br>VAT ({{ $invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR')) }})</div>
            </div>

            <!-- Item Rows - Dynamic -->
            @php $totalExcludingVat = 0; @endphp
            @foreach($invoice->items as $i => $item)
                @php
                    $itemAmountNoVat = $item->amount + $item->sscl_amount;
                    if($item->type === 'item') {
                        $totalExcludingVat += $itemAmountNoVat;
                    }
                @endphp

                <div class="flex text-[13px] min-h-[45px] invoice-item-row">
                    <div class="p-2 w-[8%] border-l border-r border-b border-black text-center flex items-center justify-center">{{ $i + 1 }}</div>
                    <div class="p-2 w-[42%] border-r border-b border-black text-left flex items-start pl-4 py-2">
                        <div class="quill-content w-full">
                            {!! $item->description !!}
                        </div>
                    </div>
                    <div class="p-2 w-[12%] border-r border-b border-black text-center flex items-center justify-center">{{ number_format($item->quantity, 0) }}</div>
                    @php
                        $displayUnitPrice = $item->quantity != 0 ? ($item->amount + $item->sscl_amount) / $item->quantity : $item->unit_price;
                        $amountIncludingVat = $displayUnitPrice * $item->quantity;
                    @endphp
                    <div class="p-2 w-[18%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ number_format($displayUnitPrice, 2) }}</div>
                    <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ number_format($amountIncludingVat, 2) }}</div>
                </div>
            @endforeach

            <!-- Totals Section -->
            <div class="flex text-[13px] font-bold min-h-[35px] invoice-totals-row">
                <div class="p-2 w-[80%] border-l border-r border-b border-black text-right pr-3 flex items-center justify-end">Total Value of Supply:</div>
                <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ number_format($totalExcludingVat, 2) }}</div>
            </div>
            @php
                $vatApplicable = $invoice->vat_applicable ?? ($invoice->estimate ? $invoice->estimate->vat_applicable : false);
                $vatRate = \App\Models\Setting::get('vat_rate', 15);
                $totalVat = $vatApplicable ? ($totalExcludingVat * ($vatRate / 100)) : 0;
                $grandTotalIncludingVat = $totalExcludingVat + $totalVat;
            @endphp
            @if($vatApplicable)
            <div class="flex text-[13px] font-bold min-h-[35px] invoice-totals-row">
                <div class="p-2 w-[80%] border-l border-r border-b border-black text-right pr-3 flex items-center justify-end">VAT Amount (Total Value of Supply @ {{ number_format($vatRate, 2) }}%):</div>
                <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ number_format($totalVat, 2) }}</div>
            </div>
            @endif
            <div class="flex text-[13px] font-bold min-h-[35px] invoice-totals-row">
                <div class="p-2 w-[80%] border-l border-r border-b border-black text-right pr-3 uppercase flex items-center justify-end">
                    {{ $vatApplicable ? 'TOTAL AMOUNT INCLUDING VAT:' : 'TOTAL AMOUNT:' }}
                </div>
                <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ $invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR')) }} {{ number_format($grandTotalIncludingVat, 2) }}</div>
            </div>
            @if(!$invoice->is_proforma)
            <div class="flex text-[13px] font-bold min-h-[35px] invoice-totals-row">
                <div class="p-2 w-[80%] border-l border-r border-b border-black text-right pr-3 uppercase flex items-center justify-end">Advance Received amount:</div>
                <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ $invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR')) }} {{ number_format($invoice->advance_received_amount ?? ($invoice->estimate->advance_received_amount ?? 0), 2) }}</div>
            </div>
            <div class="flex text-[13px] font-bold min-h-[35px] invoice-totals-row">
                <div class="p-2 w-[80%] border-l border-r border-b border-black text-right pr-3 uppercase flex items-center justify-end">Balance Payable:</div>
                <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ $invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR')) }} {{ number_format($grandTotalIncludingVat - ($invoice->advance_received_amount ?? ($invoice->estimate->advance_received_amount ?? 0)), 2) }}</div>
            </div>
            @endif

            @if($invoice->is_proforma)
                @php
                    $percentage = $invoice->proforma_percentage ?? ($invoice->estimate->proforma_percentage ?? 50);
                    $isWithTax = ($invoice->proforma_tax ?? ($invoice->estimate->proforma_tax ?? 'with_tax')) === 'with_tax';
                    $baseForAdvance = $isWithTax ? $invoice->total_amount : $totalExcludingVat;
                    $advanceAmount = ($baseForAdvance * $percentage) / 100;
                @endphp
                <div class="flex text-[13px] font-bold min-h-[35px] invoice-totals-row">
                    <div class="p-2 w-[80%] border-l border-r border-b border-black text-right pr-3 flex items-center justify-end">
                        {{ (int)$percentage }}% Advance Payable
                    </div>
                    <div class="p-2 w-[20%] border-r border-b border-black text-right pr-3 flex items-center justify-end">{{ $invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR')) }} {{ number_format($advanceAmount, 2) }}</div>
                </div>
            @else
                <div class="p-3 border-l border-r border-b border-black align-top min-h-[60px] invoice-totals-row">
                    <div class="font-bold mb-1 text-[13px]">Total Amount in words:</div>
                    <div class="text-[13px]">
                        {{ \App\Helpers\NumberToWordsHelper::translate($grandTotalIncludingVat) }} {{ ($invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR'))) === 'LKR' ? 'Rupees' : ($invoice->currency ?: ($invoice->estimate->deal->currency ?? ($invoice->estimate->currency ?? 'LKR'))) }} Only
                    </div>
                </div>
            @endif

            @if(trim($invoice->special_terms ?: ($invoice->estimate->special_terms ?? '')) !== '')
            <div class="flex border-l border-r border-b border-black align-top invoice-totals-row">
                <!-- Left Side: Mode of Payment & General Terms -->
                <div class="w-1/2 p-3">
                    <div class="mb-1">
                        <span class="font-bold text-[13px]">Mode of Payment:</span> <span class="text-[13px]">Cheque / Bank Transfer</span>
                    </div>
                    <div class="text-[13px] text-black mt-2">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Cheques to be drawn in favour of "Loops Digital Private Limited"</li>
                            <li>All relevant Government taxes will be applicable</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Right Side: Special Terms with a vertical breaker -->
                <div class="w-1/2 p-3 border-l border-black">
                    <div class="mb-1">
                        <span class="font-bold text-[13px]">Special Terms:</span>
                    </div>
                    <div class="text-[13px] text-black mt-2">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach(explode("\n", str_replace("\r", "", $invoice->special_terms ?: ($invoice->estimate->special_terms ?? ''))) as $term)
                                @if(trim($term) !== '')
                                    <li>{{ ltrim(trim($term), '-*• ') }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @else
            <div class="p-3 border-l border-r border-b border-black align-top invoice-totals-row">
                <div class="mb-1">
                    <span class="font-bold text-[13px]">Mode of Payment:</span> <span class="text-[13px]">Cheque / Bank Transfer</span>
                </div>
                <div class="text-[13px] text-black mt-2">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Cheques to be drawn in favour of "Loops Digital Private Limited"</li>
                        <li>All relevant Government taxes will be applicable</li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
        <!-- END MAIN GRID -->

        <!-- Computer Generated Invoice Footer Message -->
        <div class="invoice-footer text-left text-xs text-gray-500 mt-4 font-medium italic border-t border-gray-300 pt-3">
            This is a computer generated invoice, No manual signature requires
        </div>

    </div>
    </div>
    <style>
        /* Base styles for BOTH screen and print to ensure perfect match */
        #invoice-container, #paginated-invoice-view, .a4-page, .a4-page * { 
            background-color: #fff !important;
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
        }
        .bg-black { background-color: #000 !important; }
        .bg-white { background-color: #fff !important; }

        @media screen {
            body { background: #f3f4f6; padding: 20px 0; }
            #invoice-container, .a4-page { 
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                margin-bottom: 30px;
            }
        }

        @page {
            size: A4;
            margin: 0mm;
        }

        @media print {
            body { background: white !important; padding: 0 !important; margin: 0 !important; }
            body * { visibility: hidden; }
            
            #paginated-invoice-view, #paginated-invoice-view * {
                visibility: visible;
            }
            #paginated-invoice-view {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            #paginated-invoice-view .a4-page {
                box-shadow: none !important;
                border: 1px solid black !important;
                border-radius: 0 !important;
                margin: 0 !important;
                page-break-after: always;
                break-after: page;
                width: 210mm !important;
                height: 297mm !important;
                position: relative !important;
                display: block !important;
                box-sizing: border-box !important;
            }

            body:not(.has-paginated-view) #invoice-container, 
            body:not(.has-paginated-view) #invoice-container * { 
                visibility: visible; 
            }
            body:not(.has-paginated-view) #invoice-container { 
                display: block !important;
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 210mm !important; 
                margin: 0 !important; 
                padding: 24px !important; 
                box-sizing: border-box; 
                box-shadow: none !important; 
                min-height: 297mm !important;
                height: auto !important;
            }
            .no-print { display: none !important; }
        }

        /* PDF Generation Specific Styles to strip screen spacing/margins/shadows */
        body.generating-pdf {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        body.generating-pdf #paginated-invoice-view {
            margin: 0 !important;
            padding: 0 !important;
        }
        body.generating-pdf .a4-page {
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
            height: 296.5mm !important;
            page-break-after: always;
            break-after: page;
        }
        body.generating-pdf .a4-page:last-child {
            page-break-after: avoid !important;
            break-after: avoid !important;
        }
    </style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        // Toggle PDF generation styles to remove screen margins and shadows
        document.body.classList.add('generating-pdf');

        const element = document.getElementById('paginated-invoice-view') || document.getElementById('invoice-container');
        
        const btn = document.getElementById('download-btn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Generating PDF...';
        
        const opt = {
            margin:       0,
            filename:     'Invoice-{{ $invoice->invoice_number }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true,
                logging: false,
                letterRendering: true
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['css', 'legacy'] }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            document.body.classList.remove('generating-pdf');
        }).catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = originalText;
            document.body.classList.remove('generating-pdf');
            alert('Failed to generate PDF. Please try again.');
        });
    }

    window.addEventListener("load", function () {
        // Only run on desktop screen mode
        if (window.matchMedia("(max-width: 768px)").matches) return;
        
        const rawContainer = document.getElementById('invoice-container');
        if (!rawContainer) return;

        // A4 dimensions in pixels (at 96 DPI: 1mm = 3.779527559px)
        const mmToPx = 3.779527559;
        const pageHeight = 297 * mmToPx; // 1122.5px
        const padding = 24; // p-[24px] is 24px padding (top and bottom)
        const borderSpacing = 2; // for borders

        // Clone the sections
        const header = rawContainer.querySelector('.invoice-header').cloneNode(true);
        
        // We want to clone Date, Supplier, Delivery, Info sections
        const dateSec = rawContainer.querySelector('.invoice-date-section').cloneNode(true);
        const supplierSec = rawContainer.querySelector('.invoice-supplier-section').cloneNode(true);
        const deliverySec = rawContainer.querySelector('.invoice-delivery-section').cloneNode(true);
        const infoSec = rawContainer.querySelector('.invoice-info-section').cloneNode(true);
        
        const tableHeader = rawContainer.querySelector('.invoice-table-header').cloneNode(true);
        const itemRows = Array.from(rawContainer.querySelectorAll('.invoice-item-row')).map(row => row.cloneNode(true));
        const totalsRows = Array.from(rawContainer.querySelectorAll('.invoice-totals-row')).map(row => row.cloneNode(true));
        const footerEl = rawContainer.querySelector('.invoice-footer') ? rawContainer.querySelector('.invoice-footer').cloneNode(true) : null;

        // Create a temporary element to measure heights in the exact same environment
        const measureContainer = document.createElement('div');
        measureContainer.id = 'measure-container';
        measureContainer.style.position = 'absolute';
        measureContainer.style.visibility = 'hidden';
        measureContainer.style.width = '210mm';
        measureContainer.style.padding = '24px';
        measureContainer.style.boxSizing = 'border-box';
        measureContainer.style.border = '1px solid black';
        document.body.appendChild(measureContainer);

        function measureHeight(node) {
            measureContainer.innerHTML = '';
            const clone = node.cloneNode(true);
            if (clone.classList.contains('invoice-item-row') || clone.classList.contains('invoice-totals-row') || clone.classList.contains('invoice-table-header')) {
                const tempGrid = document.createElement('div');
                tempGrid.className = 'border-t border-l border-black';
                tempGrid.appendChild(clone);
                measureContainer.appendChild(tempGrid);
                return tempGrid.offsetHeight;
            } else {
                measureContainer.appendChild(clone);
                return clone.offsetHeight;
            }
        }

        // Measure footer height dynamically and set maxUsableHeight with a safety buffer
        const footerHeight = footerEl ? measureHeight(footerEl) : 40;
        const maxUsableHeight = pageHeight - (padding * 2) - borderSpacing - footerHeight - 25; // ~1007px

        // Create container for paginated pages
        const paginatedView = document.createElement('div');
        paginatedView.id = 'paginated-invoice-view';
        paginatedView.className = 'w-full no-print';

        let currentPage = 1;
        let pageDiv = createPageElement(currentPage);
        paginatedView.appendChild(pageDiv);

        // Page 1 gets header
        pageDiv.querySelector('.page-content').appendChild(header);

        // Create grid wrapper on Page 1
        let currentGrid = document.createElement('div');
        currentGrid.className = 'border-t border-black';
        currentGrid.appendChild(dateSec);
        currentGrid.appendChild(supplierSec);
        currentGrid.appendChild(deliverySec);
        currentGrid.appendChild(infoSec);
        currentGrid.appendChild(tableHeader.cloneNode(true));
        pageDiv.querySelector('.page-content').appendChild(currentGrid);

        let currentPageHeight = measureHeight(pageDiv.querySelector('.page-content')) - (padding * 2);

        // Distribute item rows
        const queue = [...itemRows];
        while (queue.length > 0) {
            const row = queue.shift();
            const rowHeight = measureHeight(row);

            if (currentPageHeight + rowHeight > maxUsableHeight) {
                // Row doesn't fit on current page! Create Page 2.
                currentPage++;
                pageDiv = createPageElement(currentPage);
                paginatedView.appendChild(pageDiv);

                currentGrid = document.createElement('div');
                currentGrid.className = 'border-t border-black';
                currentGrid.appendChild(tableHeader.cloneNode(true));
                pageDiv.querySelector('.page-content').appendChild(currentGrid);
                
                currentGrid.appendChild(row);
                currentPageHeight = measureHeight(pageDiv.querySelector('.page-content')) - (padding * 2);
            } else {
                currentGrid.appendChild(row);
                currentPageHeight += rowHeight;
            }
        }

        // Add Totals Rows
        for (const row of totalsRows) {
            const rowHeight = measureHeight(row);
            if (currentPageHeight + rowHeight > maxUsableHeight) {
                // Totals row doesn't fit! Create Page.
                currentPage++;
                pageDiv = createPageElement(currentPage);
                paginatedView.appendChild(pageDiv);

                currentGrid = document.createElement('div');
                currentGrid.className = 'border-t border-black';
                pageDiv.querySelector('.page-content').appendChild(currentGrid);
                
                currentGrid.appendChild(row);
                currentPageHeight = measureHeight(pageDiv.querySelector('.page-content')) - (padding * 2);
            } else {
                currentGrid.appendChild(row);
                currentPageHeight += rowHeight;
            }
        }

        // Add footer to the last page
        if (footerEl) {
            pageDiv.querySelector('.page-content').appendChild(footerEl);
        }

        // Clean up measurement container
        document.body.removeChild(measureContainer);

        // Hide original container and insert paginated view
        rawContainer.style.display = 'none';
        rawContainer.parentNode.insertBefore(paginatedView, rawContainer.nextSibling);
        document.body.classList.add('has-paginated-view');

        // Helper to create page elements
        function createPageElement(pageNum) {
            const div = document.createElement('div');
            div.className = 'a4-page bg-white border border-black my-4 mx-auto relative';
            div.style.width = '210mm';
            div.style.height = '297mm';
            div.style.boxSizing = 'border-box';
            div.style.position = 'relative';
            
            const inner = document.createElement('div');
            inner.className = 'page-content p-[24px]';
            inner.style.height = '100%';
            inner.style.boxSizing = 'border-box';
            div.appendChild(inner);

            return div;
        }
    });

</script>
@endpush
