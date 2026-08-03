@extends('layouts.app')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        #items-body tr.sortable-ghost {
            opacity: 0.4;
            background: #f0f9ff;
        }

        .drag-handle {
            cursor: grab;
            color: #CBD5E1;
        }

        .drag-handle:hover {
            color: #94A3B8;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Override Quill header labels */
        .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="1"]::before,
        .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="1"]::before {
            content: 'Heading' !important;
        }
        .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="2"]::before,
        .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="2"]::before {
            content: 'Sub heading' !important;
        }
    </style>
@endpush

@section('header')
    <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-edit text-brand-pink"></i> 
            {{ (request('from') === 'invoice' || in_array($estimate->status, ['invoiced', 'approved'])) ? 'Edit Invoice' : 'Edit Estimate' }} 
            <span class="text-gray-400 text-sm font-normal ml-2">#{{ $estimate->reference_number }}</span>
        </h2>
        <a href="{{ route('estimates.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
            Cancel
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto my-8 px-4 sm:px-6 lg:px-8">
        <form id="estimate-form" action="{{ route('estimates.update', $estimate->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-6 flex items-center bg-white border border-gray-200 p-3 shadow-sm rounded-md max-w-fit">
                    <div class="bg-orange-500 text-white w-8 h-8 flex items-center justify-center rounded mr-4 flex-shrink-0">
                        <i class="fas fa-exclamation text-lg font-bold"></i>
                    </div>
                    <div class="text-sm text-gray-800 font-medium">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif


            @if(isset($readonly) && $readonly)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 font-bold">
                                @if(!$estimate->canEdit())
                                    You have view-only access to this estimate because your department is in the project split.
                                @else
                                    This estimate is {{ str_replace('_', ' ', $estimate->status) }}, so it can no longer be modified.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Main Area (2/3 Column) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Section: Client Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">Client Details</h3>
                            <i class="fas fa-user-tie text-gray-400"></i>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Customer Select -->
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <label class="col-span-12 sm:col-span-3 text-right text-sm font-medium text-gray-600">Customer <span class="text-red-500">*</span></label>
                                <div class="col-span-12 sm:col-span-9">
                                    <select name="customer_id" required data-required="true"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                        <option value="">-- Select Customer --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $estimate->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Brand Name -->
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <label class="col-span-12 sm:col-span-3 text-right text-sm font-medium text-gray-600">Brand Name <span class="text-red-500">*</span></label>
                                <div class="col-span-12 sm:col-span-9 brand-name-tom-select">
                                    <select name="brand_name" id="brand_name_select" required data-required="true"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                        <option value="">-- No Brand / Select Brand --</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand }}" {{ $estimate->brand_name == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                                        @endforeach
                                        @if($estimate->brand_name && !$brands->contains($estimate->brand_name))
                                            <option value="{{ $estimate->brand_name }}" selected>{{ $estimate->brand_name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <style>
                                .brand-name-tom-select .ts-wrapper .ts-control {
                                    border: none !important;
                                    box-shadow: none !important;
                                }
                            </style>

                            <!-- Attention To -->
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <label class="col-span-12 sm:col-span-3 text-right text-sm font-medium text-gray-600">Attention To <span class="text-red-500">*</span></label>
                                <div class="col-span-12 sm:col-span-9">
                                    <input type="text" name="attention_to" value="{{ old('attention_to', $estimate->attention_to) }}" placeholder="E.g. Mr. John Doe" required data-required="true"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                </div>
                            </div>

                            <!-- Designation -->
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <label class="col-span-12 sm:col-span-3 text-right text-sm font-medium text-gray-600">Designation</label>
                                <div class="col-span-12 sm:col-span-9">
                                    <input type="text" name="designation" value="{{ old('designation', $estimate->designation) }}" placeholder="E.g. Senior Manager"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                </div>
                            </div>

                            <!-- Address Group -->
                            <div class="grid grid-cols-12 gap-4 items-start">
                                <label class="col-span-12 sm:col-span-3 text-right text-sm font-medium text-gray-600 pt-2">Address</label>
                                <div class="col-span-12 sm:col-span-9 space-y-2">
                                    <input type="text" name="address_line_1" value="{{ old('address_line_1', $estimate->address_line_1) }}" placeholder="Address Line 1"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                    <input type="text" name="address_line_2" value="{{ old('address_line_2', $estimate->address_line_2) }}" placeholder="Address Line 2"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                    <input type="text" name="address_line_3" value="{{ old('address_line_3', $estimate->address_line_3) }}" placeholder="Address Line 3"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm shadow-sm py-2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Estimate Items -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">Estimate Items</h3>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="sscl_applicable" id="sscl_applicable" value="1" {{ $estimate->sscl_applicable ? 'checked' : '' }} onchange="calculateAllRows()"
                                        class="rounded border-gray-300 text-brand-purple shadow-sm focus:ring-brand-purple">
                                    <span class="ml-2 text-xs font-semibold text-gray-600">SSCL ({{ number_format($ssclRate, 4) }}%)</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="vat_applicable" id="vat_applicable" value="1" {{ $estimate->vat_applicable ? 'checked' : '' }} onchange="calculateAllRows()"
                                        class="rounded border-gray-300 text-brand-purple shadow-sm focus:ring-brand-purple">
                                    <span class="ml-2 text-xs font-semibold text-gray-600">VAT ({{ number_format($vatRate, 2) }}%)</span>
                                </label>
                            </div>
                        </div>
                        <div class="p-0 overflow-x-auto min-h-[250px]">
                            <table class="w-full min-w-[700px]" id="items-table">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-2 py-3 w-8"></th>
                                        <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase">Description</th>
                                        <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Department</th>
                                        <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Rev. Category</th>
                                        <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase w-20">Qty</th>
                                        <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase w-32">Unit Price</th>
                                        <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase w-32">Amount</th>
                                        <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50" id="items-body">
                                    @foreach($estimate->items as $index => $item)
                                        <tr class="group hover:bg-gray-50 transition-colors">
                                            <td class="p-2 align-middle text-center drag-handle" title="Drag to reorder">
                                                <i class="fas fa-grip-vertical text-gray-300"></i>
                                            </td>
                                            <td class="p-2 align-top">
                                                <div class="description-preview quill-content border border-gray-200 rounded p-2 text-xs text-gray-500 cursor-pointer hover:bg-gray-50 bg-white" 
                                                     style="min-height: 40px; max-height: 80px; overflow-y: auto;" 
                                                     onclick="openDescriptionModal(this)">
                                                    @if($item->description)
                                                        {!! $item->description !!}
                                                    @else
                                                        <span class="text-gray-400 italic">Click to edit description...</span>
                                                    @endif
                                                </div>
                                                <input type="hidden" name="items[{{ $index }}][description]" class="description-hidden-input" value="{{ $item->description }}" required data-required="true">
                                            </td>
                                            <td class="p-2 align-top">
                                                <select name="items[{{ $index }}][department]" required data-required="true" class="w-full rounded-md border-gray-200 text-xs py-1 px-1">
                                                    <option value="">Select</option>
                                                    <option value="creative" {{ $item->department == 'creative' ? 'selected' : '' }}>Creative</option>
                                                    <option value="digital" {{ $item->department == 'digital' ? 'selected' : '' }}>Digital</option>
                                                    <option value="play" {{ $item->department == 'play' ? 'selected' : '' }}>Play</option>
                                                    <option value="tech" {{ $item->department == 'tech' ? 'selected' : '' }}>Tech</option>
                                                    <option value="PM" {{ $item->department == 'PM' ? 'selected' : '' }}>PM</option>
                                                    <option value="Corporate" {{ $item->department == 'Corporate' ? 'selected' : '' }}>Corporate</option>
                                                </select>
                                            </td>
                                            <td class="p-2 align-top">
                                                <select name="items[{{ $index }}][revenue_category]" required data-required="true" class="w-full rounded-md border-gray-200 text-xs py-1 px-1">
                                                    <option value="">Select</option>
                                                    <option value="Retainer" {{ $item->revenue_category == 'Retainer' ? 'selected' : '' }}>Retainer</option>
                                                    <option value="Ads" {{ $item->revenue_category == 'Ads' ? 'selected' : '' }}>Ads</option>
                                                    <option value="Campaigns/Projects" {{ $item->revenue_category == 'Campaigns/Projects' ? 'selected' : '' }}>Campaigns/Projects</option>
                                                    <option value="CAG" {{ $item->revenue_category == 'CAG' ? 'selected' : '' }}>CAG</option>
                                                    <option value="Corporate" {{ $item->revenue_category == 'Corporate' ? 'selected' : '' }}>Corporate</option>
                                                </select>
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="hidden" name="items[{{ $index }}][position]" value="{{ $item->position ?? $index }}">
                                                <input type="number" name="items[{{ $index }}][quantity]" required data-required="true" value="{{ isset($item->quantity) ? $item->quantity : '' }}" placeholder="1" oninput="calculateRow(this)"
                                                    class="w-full rounded-md border-gray-200 text-sm py-1 px-1 text-right">
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" required data-required="true" 
                                                    value="{{ isset($item->unit_price) ? number_format((float)$item->unit_price, 2, '.', '') : '' }}" 
                                                    placeholder="0.00" oninput="calculateRow(this)"
                                                    class="w-full rounded-md border-gray-200 text-sm py-1 px-1 text-right">
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="number" step="0.01" name="items[{{ $index }}][amount]" value="{{ isset($item->total_with_vat) ? number_format((float)$item->total_with_vat, 2, '.', '') : '' }}" placeholder="0.00" readonly
                                                    class="w-full border-none bg-transparent text-sm py-1 px-1 text-right font-medium text-gray-700">
                                            </td>
                                            <td class="p-2 align-top text-center">
                                                <button type="button" onclick="this.closest('tr').remove(); calculateTotals();"
                                                    class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="p-4 bg-gray-50 border-t border-gray-100">
                                @if(!(isset($readonly) && $readonly))
                                <button type="button" onclick="addItem()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-brand-purple bg-brand-purple bg-opacity-10 hover:bg-opacity-20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple transition-colors">
                                    <i class="fas fa-plus mr-2"></i> Add Item
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Section: Third Party Costs -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">Third Party Costs</h3>
                                <span class="text-red-500">*</span>
                            </div>
                            <div class="flex items-center gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="third_party_cost" value="yes" {{ ($estimate->third_party_cost ?? 'no') == 'yes' ? 'checked' : '' }} onchange="toggleThirdPartySection(this.value)"
                                        class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue">
                                    <span class="text-xs font-semibold text-gray-600">Yes</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="third_party_cost" value="no" {{ ($estimate->third_party_cost ?? 'no') == 'no' ? 'checked' : '' }} onchange="toggleThirdPartySection(this.value)"
                                        class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue">
                                    <span class="text-xs font-semibold text-gray-600">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Integrated Third Party Costs Table -->
                        <div id="third_party_costs_section" class="{{ ($estimate->third_party_cost ?? 'no') == 'no' ? 'hidden' : '' }} p-0 overflow-x-auto">
                            <table class="w-full min-w-[700px]" id="third-party-table">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase">Supplier <span class="text-red-500">*</span></th>
                                        <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase w-32">Cost <span class="text-red-500">*</span></th>
                                        <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase w-40">Department <span class="text-red-500">*</span></th>
                                        <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase w-48">File <span class="text-red-500">*</span></th>
                                        <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50" id="third-party-body">
                                    @foreach($estimate->thirdPartyCosts as $cIndex => $cost)
                                        <tr class="group hover:bg-gray-50 transition-colors">
                                            <td class="p-2 align-top">
                                                <input type="hidden" name="third_party_costs[{{ $cIndex }}][id]" value="{{ $cost->id }}">
                                                <input type="text" name="third_party_costs[{{ $cIndex }}][supplier]" value="{{ $cost->supplier }}" required placeholder="Supplier Name" class="w-full rounded-md border border-gray-300 text-sm py-1.5 px-3 font-mono">
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="number" step="0.01" name="third_party_costs[{{ $cIndex }}][cost]" value="{{ $cost->cost }}" required placeholder="0.00" class="w-full rounded-md border border-gray-300 text-sm py-1.5 px-3 text-right font-mono">
                                            </td>
                                            <td class="p-2 align-top">
                                                <select name="third_party_costs[{{ $cIndex }}][department]" required class="w-full rounded-md border border-gray-300 text-sm py-1.5 px-3 font-mono">
                                                    <option value="">Select</option>
                                                    <option value="creative" {{ $cost->department == 'creative' ? 'selected' : '' }}>Creative</option>
                                                    <option value="digital" {{ $cost->department == 'digital' ? 'selected' : '' }}>Digital</option>
                                                    <option value="play" {{ $cost->department == 'play' ? 'selected' : '' }}>Play</option>
                                                    <option value="tech" {{ $cost->department == 'tech' ? 'selected' : '' }}>Tech</option>
                                                    <option value="PM" {{ $cost->department == 'PM' ? 'selected' : '' }}>PM</option>
                                                    <option value="Corporate" {{ $cost->department == 'Corporate' ? 'selected' : '' }}>Corporate</option>
                                                </select>
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="file" name="third_party_costs[{{ $cIndex }}][file]" {{ !$cost->file_path ? 'required' : '' }} class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border file:border-gray-300 file:text-xs file:bg-brand-blue file:bg-opacity-10 file:text-brand-blue">
                                                @if($cost->file_path)
                                                    <div class="existing-file-info">
                                                        <a href="{{ Storage::url($cost->file_path) }}" target="_blank" class="text-xs text-brand-blue hover:underline mt-1 block">
                                                            <i class="fas fa-paperclip mr-0.5"></i> View Existing
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="p-2 align-middle text-center">
                                                <button type="button" onclick="this.closest('tr').remove()" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if(!(isset($readonly) && $readonly))
                            <div class="p-4 bg-gray-50 border-t border-gray-100">
                                <button type="button" onclick="addThirdPartyCost()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-brand-blue bg-brand-blue bg-opacity-10 hover:bg-opacity-20 transition-colors">
                                    <i class="fas fa-plus mr-2"></i> Add Cost
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Section: Summary - REQUESTED IN MAIN AREA -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">Summary</h3>
                            <i class="fas fa-calculator text-gray-400"></i>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 font-medium">Subtotal</span>
                                        <span class="font-bold text-gray-700 font-mono" id="display_subtotal">0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 font-medium">SSCL Amount</span>
                                        <span class="font-bold text-gray-700 font-mono" id="display_sscl">0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm border-b border-gray-100 pb-3">
                                        <span class="text-gray-500 font-medium">VAT Amount</span>
                                        <span class="font-bold text-gray-700 font-mono" id="display_vat">0.00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 font-medium">Discount</span>
                                        <span class="font-bold text-gray-700 font-mono" id="display_discount">0.00</span>
                                    </div>
                                </div>
                                <div class="flex flex-col justify-center items-end border-l border-gray-50 pl-8">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Grand Total</span>
                                    <span class="text-4xl font-black text-brand-pink font-mono" id="display_total">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Area (1/3 Column) -->
                <div class="lg:col-span-1 space-y-8">
                    
                    <!-- Section: General Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">General Info</h3>
                            <i class="fas fa-info-circle text-gray-400"></i>
                        </div>
                        <div class="p-6 space-y-5">
                            <!-- Reference Number -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Estimate Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hashtag text-gray-300 text-xs"></i>
                                    </div>
                                    <input type="text" value="{{ $estimate->reference_number }}" readonly
                                        class="w-full pl-8 bg-gray-50 rounded-md border-gray-200 text-gray-500 text-sm py-2 font-mono font-bold"
                                        title="Fixed reference number">
                                </div>
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="date" value="{{ old('date', $estimate->date) }}" required data-required="true"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                            </div>

                            <!-- Currency -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Currency <span class="text-red-500">*</span></label>
                                <select name="currency" required data-required="true"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                                    <option value="">Select Currency</option>
                                    @if($currencies->isEmpty())
                                        <option value="LKR" {{ $estimate->currency == 'LKR' ? 'selected' : '' }}>LKR - Sri Lankan Rupee (Rs)</option>
                                    @else
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->code }}" {{ $estimate->currency == $currency->code ? 'selected' : '' }}>
                                                {{ $currency->code }} - {{ $currency->name }} ({{ $currency->symbol }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Heading -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Project Heading</label>
                                <input type="text" name="heading" value="{{ old('heading', $estimate->heading) }}" placeholder="E.g. Web Development"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                            </div>

                            <!-- Invoice Type -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Invoice Type <span class="text-red-500">*</span></label>
                                <select name="invoice_type" id="invoice_type" required
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                                    <option value="invoice" {{ ($estimate->invoice_type ?? 'tax_invoice') == 'invoice' ? 'selected' : '' }}>Invoice</option>
                                    <option value="tax_invoice" {{ ($estimate->invoice_type ?? 'tax_invoice') == 'tax_invoice' ? 'selected' : '' }}>Tax Invoice</option>
                                </select>
                            </div>

                            <!-- Date of Delivery -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date of Delivery</label>
                                <input type="date" name="date_of_delivery" value="{{ old('date_of_delivery', $estimate->date_of_delivery) }}"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                            </div>

                            <!-- Place of Supply -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Place of Supply</label>
                                <input type="text" name="place_of_supply" value="{{ old('place_of_supply', $estimate->place_of_supply) }}" placeholder="E.g. Colombo, Sri Lanka"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Terms & Payment - REQUESTED IN SIDEBAR -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">Terms & Payment</h3>
                            <i class="fas fa-file-contract text-gray-400"></i>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Standard Terms -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Standard Terms</label>
                                <select id="term_selector" onchange="addTerm(this.value); this.value='';"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-xs py-2 shadow-sm">
                                    <option value="">-- Add Standard Terms --</option>
                                    @foreach($standardTerms as $term)
                                        <option value="{{ $term->content }}">{{ Str::limit($term->content, 50) }}</option>
                                    @endforeach
                                </select>
                                <div id="selected_terms_container" class="mt-3 space-y-2">
                                    <!-- Selected terms will appear here -->
                                </div>
                            </div>

                            <!-- Proforma? -->
                            <div class="pt-4 border-t border-gray-50">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Proforma Invoice? <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-6 mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="proforma_invoice" value="yes" {{ ($estimate->proforma_invoice ?? 'yes') == 'yes' ? 'checked' : '' }} onchange="toggleProformaFields(this.value)"
                                            class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue">
                                        <span class="text-sm font-medium text-gray-700">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="proforma_invoice" value="no" {{ ($estimate->proforma_invoice ?? 'yes') == 'no' ? 'checked' : '' }} onchange="toggleProformaFields(this.value)"
                                            class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue">
                                        <span class="text-sm font-medium text-gray-700">No</span>
                                    </label>
                                </div>
                                <div id="proforma_details" class="{{ ($estimate->proforma_invoice ?? 'yes') == 'no' ? 'hidden' : '' }} space-y-3 pt-4 border-t border-gray-50">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Proforma Percentage %</label>
                                        <input type="number" step="1" name="proforma_percentage" value="{{ old('proforma_percentage', (int)$estimate->proforma_percentage) }}" placeholder="Percentage %"
                                            class="w-full rounded-md border-gray-200 text-sm py-1.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Proforma Tax Setting</label>
                                        <select name="proforma_tax" class="w-full rounded-md border-gray-200 text-xs py-1.5 px-3">
                                            <option value="with_tax" {{ ($estimate->proforma_tax ?? 'with_tax') == 'with_tax' ? 'selected' : '' }}>With Tax</option>
                                            <option value="without_tax" {{ ($estimate->proforma_tax ?? 'with_tax') == 'without_tax' ? 'selected' : '' }}>Without Tax</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Advance Received Amount -->
                            <div class="pt-4 border-t border-gray-50">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Advance Received Amount</label>
                                <input type="number" step="0.01" name="advance_received_amount" value="{{ old('advance_received_amount', $estimate->advance_received_amount) }}" placeholder="0.00"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2 shadow-sm">
                            </div>

                            <!-- PO Applicable? -->
                            <div class="pt-4 border-t border-gray-50">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">PO Applicable? <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-6 mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="po_applicable" value="yes" {{ ($estimate->po_applicable ?? 'no') == 'yes' ? 'checked' : '' }} onchange="togglePoSection(this.value)"
                                            class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue">
                                        <span class="text-sm font-medium text-gray-700">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="po_applicable" value="no" {{ ($estimate->po_applicable ?? 'no') == 'no' ? 'checked' : '' }} onchange="togglePoSection(this.value)"
                                            class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue">
                                        <span class="text-sm font-medium text-gray-700">No</span>
                                    </label>
                                </div>

                                <div id="po_details" class="{{ ($estimate->po_applicable ?? 'no') == 'no' ? 'hidden' : '' }} space-y-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">PO Number <span class="text-red-500">*</span></label>
                                        <input type="text" name="po_number" id="po_number" value="{{ old('po_number', $estimate->po_number) }}" placeholder="Enter PO Number"
                                            class="w-full rounded-md border-gray-200 text-sm py-1.5 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">PO Document</label>
                                        <input type="file" name="po_document" id="po_document"
                                            class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-brand-blue file:bg-opacity-10 file:text-brand-blue">
                                        @if($estimate->po_file_path)
                                            <a href="{{ asset($estimate->po_file_path) }}" target="_blank" class="text-xs text-brand-blue hover:underline mt-1 block font-medium">
                                                <i class="fas fa-file-pdf mr-1"></i> View Current PO document
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                                <!-- Special Terms -->
                                <div class="pt-4 border-t border-gray-50">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Special Terms</label>
                                    <select onchange="appendSpecialTerm(this.value); this.value='';"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-xs py-2 shadow-sm mb-2">
                                        <option value="">-- Load Standard Term --</option>
                                        @foreach($standardTerms as $term)
                                            <option value="{{ $term->content }}">{{ Str::limit($term->content, 50) }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="special_terms" rows="3" placeholder="Any custom conditions..."
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">{{ old('special_terms', $estimate->special_terms) }}</textarea>
                                </div>
                            </div>
                        </div>

                    <!-- Section: Approval - REQUESTED IN SIDEBAR -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-800 uppercase tracking-wider text-xs">Approval</h3>
                            <i class="fas fa-signature text-gray-400"></i>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Senior Manager -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Senior Manager <span class="text-red-500">*</span></label>
                                <select name="senior_manager" required data-required="true"
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">
                                    <option value="">-- Select Senior Manager --</option>
                                    @foreach($users as $user)
                                        @php
                                            $isSelected = old('senior_manager', $estimate->senior_manager) == $user->name;
                                            if (!$estimate->senior_manager && $estimate->deal) {
                                                $dealOwner = $estimate->deal->senior_manager ?? ($estimate->deal->owner->name ?? null);
                                                if ($dealOwner == $user->name) {
                                                    $isSelected = true;
                                                }
                                            }
                                        @endphp
                                        <option value="{{ $user->name }}" {{ $isSelected ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Note -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Internal Note</label>
                                <textarea name="additional_notes" rows="3" placeholder="Notes for the team..."
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-sm py-2">{{ old('additional_notes', $estimate->additional_notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end gap-4">
                <a href="{{ route('estimates.index') }}"
                    class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium shadow-sm transition-all">
                    {{ (isset($readonly) && $readonly) ? 'Back' : 'Cancel' }}
                </a>
                @if(!(isset($readonly) && $readonly))
                <button type="submit"
                    class="px-8 py-2.5 rounded-lg bg-brand-pink text-white hover:bg-brand-purple text-sm font-medium shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i> {{ (request('from') === 'invoice' || in_array($estimate->status, ['invoiced', 'approved'])) ? 'Update Invoice' : 'Update Estimate' }}
                </button>
                @endif
            </div>

        </form>
    </div>

    <!-- Description Modal -->
    <div id="descriptionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDescriptionModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Edit Description
                            </h3>
                            <div class="mt-2 w-full">
                                <div id="modalQuillEditor" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="saveDescriptionModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-pink text-base font-medium text-white hover:bg-brand-purple focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple sm:ml-3 sm:w-auto sm:text-sm">
                        Save Description
                    </button>
                    <button type="button" onclick="closeDescriptionModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        let modalQuillInstance = null;
        let currentDescriptionInput = null;
        let currentDescriptionPreview = null;

        function initModalQuill() {
            if (!modalQuillInstance) {
                modalQuillInstance = new Quill('#modalQuillEditor', {
                    theme: 'snow',
                    placeholder: 'Item description...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'bullet' }]
                        ]
                    }
                });
            }
        }

        function openDescriptionModal(previewElement) {
            const row = previewElement.closest('tr');
            currentDescriptionPreview = previewElement;
            currentDescriptionInput = row.querySelector('.description-hidden-input');

            if (!modalQuillInstance) {
                initModalQuill();
            }

            // Load content
            if (currentDescriptionInput.value) {
                modalQuillInstance.root.innerHTML = currentDescriptionInput.value;
            } else {
                modalQuillInstance.root.innerHTML = '';
            }

            document.getElementById('descriptionModal').classList.remove('hidden');
        }

        function closeDescriptionModal() {
            document.getElementById('descriptionModal').classList.add('hidden');
            currentDescriptionInput = null;
            currentDescriptionPreview = null;
        }

        function saveDescriptionModal() {
            if (currentDescriptionInput && currentDescriptionPreview) {
                const htmlContent = modalQuillInstance.root.innerHTML;
                const textContent = modalQuillInstance.getText().trim();
                
                currentDescriptionInput.value = htmlContent;

                if (textContent === '') {
                    currentDescriptionPreview.innerHTML = '<span class="text-gray-400 italic">Click to edit description...</span>';
                } else {
                    currentDescriptionPreview.innerHTML = htmlContent;
                }
            }
            closeDescriptionModal();
        }
        document.addEventListener('DOMContentLoaded', function() {
            initModalQuill();
        });

        function addItem() {
            const tbody = document.getElementById('items-body');
            const newIndex = new Date().getTime();

            const row = document.createElement('tr');
            row.className = "group hover:bg-gray-50 transition-colors";

            const editorId = `editor-${newIndex}`;
            const inputName = `items[${newIndex}][description]`;

            row.innerHTML = `
                <td class="p-2 align-middle text-center drag-handle" title="Drag to reorder">
                    <i class="fas fa-grip-vertical text-gray-300"></i>
                </td>
                <td class="p-2 align-top">
                    <div class="description-preview quill-content border border-gray-200 rounded p-2 text-xs text-gray-500 cursor-pointer hover:bg-gray-50 bg-white" 
                         style="min-height: 40px; max-height: 80px; overflow-y: auto;" 
                         onclick="openDescriptionModal(this)">
                        <span class="text-gray-400 italic">Click to edit description...</span>
                    </div>
                    <input type="hidden" name="${inputName}" class="description-hidden-input" required data-required="true">
                </td>
                <td class="p-2 align-top">
                    <select name="items[${newIndex}][department]" required data-required="true" class="w-full rounded-md border-gray-200 text-xs py-1 px-1">
                        <option value="">Select</option>
                        <option value="creative">Creative</option>
                        <option value="digital">Digital</option>
                        <option value="play">Play</option>
                        <option value="tech">Tech</option>
                        <option value="PM">PM</option>
                        <option value="Corporate">Corporate</option>
                    </select>
                </td>
                <td class="p-2 align-top">
                    <select name="items[${newIndex}][revenue_category]" required data-required="true" class="w-full rounded-md border-gray-200 text-xs py-1 px-1">
                        <option value="">Select</option>
                        <option value="Retainer">Retainer</option>
                        <option value="Ads">Ads</option>
                        <option value="Campaigns/Projects">Campaigns/Projects</option>
                        <option value="CAG">CAG</option>
                        <option value="Corporate">Corporate</option>
                    </select>
                </td>
                <td class="p-2 align-top">
                    <input type="hidden" name="items[${newIndex}][position]" value="${newIndex}">
                    <input type="number" name="items[${newIndex}][quantity]" required data-required="true" value="1" placeholder="1" oninput="calculateRow(this)" class="w-full rounded-md border-gray-200 text-sm py-1 px-1 text-right">
                </td>
                <td class="p-2 align-top">
                    <input type="number" step="0.01" name="items[${newIndex}][unit_price]" required data-required="true" value="" placeholder="0.00" oninput="calculateRow(this)" class="w-full rounded-md border-gray-200 text-sm py-1 px-1 text-right">
                </td>
                <td class="p-2 align-top">
                    <input type="number" step="0.01" name="items[${newIndex}][amount]" placeholder="0.00" readonly class="w-full border-none bg-transparent text-sm py-1 px-1 text-right font-medium text-gray-700">
                </td>
                <td class="p-2 align-top text-center">
                    <button type="button" onclick="this.closest('tr').remove(); calculateTotals();" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            calculateRow(row.querySelector('input[name*="[quantity]"]'));
        }

        const ssclRate = {{ $ssclRate / 100 }};
        const vatRate = {{ $vatRate / 100 }};

        function calculateRow(input) {
            const row = input.closest('tr');
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            const priceInput = row.querySelector('input[name*="[unit_price]"]');
            
            if ((qtyInput.value === '' || parseFloat(qtyInput.value) === 0) && priceInput.value !== '') {
                qtyInput.value = 1;
            }
            
            const qty = parseFloat(qtyInput.value) || 0;
            const parsedBasePrice = parseFloat(priceInput.value) || 0;

            const ssclApplicable = document.getElementById('sscl_applicable').checked;
            const vatApplicable = document.getElementById('vat_applicable').checked;

            // Round base amount
            const baseAmount = Math.round(qty * parsedBasePrice * 100) / 100;
            
            let rowSSCL = 0;
            if (ssclApplicable) {
                rowSSCL = Math.round(baseAmount * ssclRate * 100) / 100;
            }
            
            let rowVAT = 0;
            if (vatApplicable) {
                rowVAT = Math.round((baseAmount + rowSSCL) * vatRate * 100) / 100;
            }
            
            const totalWithVat = baseAmount + rowSSCL + rowVAT;
            row.querySelector('input[name*="[amount]"]').value = totalWithVat.toFixed(2);

            calculateTotals();
        }

        function calculateTotals() {
            let subtotalBase = 0;
            let totalSSCL = 0;
            let totalVAT = 0;
            let discountTotal = 0;

            const ssclApplicable = document.getElementById('sscl_applicable').checked;
            const vatApplicable = document.getElementById('vat_applicable').checked;

            document.querySelectorAll('#items-body tr').forEach(row => {
                const qty = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
                const priceInput = row.querySelector('input[name*="[unit_price]"]');
                
                const parsedBasePrice = parseFloat(priceInput.value) || 0;
                const baseAmount = Math.round(qty * parsedBasePrice * 100) / 100;

                subtotalBase += baseAmount;
                if (parsedBasePrice < 0) {
                    discountTotal += baseAmount; // baseAmount is negative
                }
                
                let rowSSCL = 0;
                if (ssclApplicable) {
                    rowSSCL = Math.round(baseAmount * ssclRate * 100) / 100;
                    totalSSCL += rowSSCL;
                }
                
                if (vatApplicable) {
                    const rowVAT = Math.round((baseAmount + rowSSCL) * vatRate * 100) / 100;
                    totalVAT += rowVAT;
                }
            });

            const grandTotal = subtotalBase + totalSSCL + totalVAT;

            const subtotalElem = document.getElementById('display_subtotal');
            const ssclElem = document.getElementById('display_sscl');
            const vatElem = document.getElementById('display_vat');
            const totalElem = document.getElementById('display_total');
            const discountElem = document.getElementById('display_discount');
            
            if (subtotalElem) subtotalElem.textContent = subtotalBase.toFixed(2);
            if (ssclElem) ssclElem.textContent = totalSSCL.toFixed(2);
            if (vatElem) vatElem.textContent = totalVAT.toFixed(2);
            if (totalElem) totalElem.textContent = grandTotal.toFixed(2);
            if (discountElem) discountElem.textContent = Math.abs(discountTotal).toFixed(2);
        }

        function calculateAllRows() {
            document.querySelectorAll('#items-body tr').forEach(row => {
                const input = row.querySelector('input[name*="[quantity]"]');
                if (input) calculateRow(input);
            });
        }

        function appendSpecialTerm(content) {
            if (!content) return;
            const textarea = document.querySelector('textarea[name="special_terms"]');
            if (textarea) {
                if (textarea.value.trim() === '') {
                    textarea.value = content;
                } else {
                    textarea.value += '\n' + content;
                }
            }
        }

        function addTerm(content) {
            if (!content) return;
            const container = document.getElementById('selected_terms_container');
            const id = 'term_' + new Date().getTime() + Math.random().toString(36).substr(2, 9);
            const div = document.createElement('div');
            div.className = "flex justify-between items-start bg-gray-50 p-2 rounded border border-gray-200 text-[11px]";
            div.id = id;
            div.innerHTML = `
                <span class="text-gray-700 leading-snug flex-1 mr-2">${content}</span>
                <input type="hidden" name="terms[]" value="${content}">
                <button type="button" onclick="document.getElementById('${id}').remove()" class="text-red-400 hover:text-red-600 focus:outline-none"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(div);
        }

        function toggleProformaFields(value) {
            const details = document.getElementById('proforma_details');
            if (!details) return;
            if (value === 'yes') {
                details.classList.remove('hidden');
            } else {
                details.classList.add('hidden');
            }
        }

        function toggleThirdPartySection(value) {
            const section = document.getElementById('third_party_costs_section');
            const inputs = section.querySelectorAll('input, select');

            if (value === 'yes') {
                section.classList.remove('hidden');
                inputs.forEach(input => {
                    const isExistingFileRow = input.closest('tr')?.querySelector('.existing-file-info');
                    
                    if (input.name.includes('[supplier]') || 
                        input.name.includes('[cost]') || 
                        input.name.includes('[department]')) {
                        input.required = true;
                    }
                    
                    if (input.name.includes('[file]') && !isExistingFileRow) {
                        input.required = true;
                    }
                });
                if (document.getElementById('third-party-body').children.length === 0) {
                    addThirdPartyCost();
                }
            } else {
                section.classList.add('hidden');
                inputs.forEach(input => input.required = false);
            }
        }

        function togglePoSection(value) {
            const details = document.getElementById('po_details');
            const poNumber = document.getElementById('po_number');
            const poDocument = document.getElementById('po_document');
            const hasExistingFile = {{ $estimate->po_file_path ? 'true' : 'false' }};
            
            if (!details) return;
            if (value === 'yes') {
                details.classList.remove('hidden');
                poNumber.required = true;
            } else {
                details.classList.add('hidden');
                poNumber.required = false;
                poDocument.required = false;
            }
        }

        function addThirdPartyCost() {
            const tbody = document.getElementById('third-party-body');
            const newIndex = 'new_' + new Date().getTime() + Math.random().toString(36).substr(2, 5);
            const row = document.createElement('tr');
            row.className = "group hover:bg-gray-50 transition-colors";

            const isRequired = document.querySelector('input[name="third_party_cost"]:checked')?.value === 'yes' ? 'required' : '';

            row.innerHTML = `
                <td class="p-2 align-top">
                    <input type="text" name="third_party_costs[${newIndex}][supplier]" ${isRequired} placeholder="Supplier Name" class="w-full rounded-md border border-gray-300 text-sm py-1.5 px-3 font-mono">
                </td>
                <td class="p-2 align-top">
                    <input type="number" step="0.01" name="third_party_costs[${newIndex}][cost]" ${isRequired} placeholder="0.00" class="w-full rounded-md border border-gray-300 text-sm py-1.5 px-3 text-right font-mono">
                </td>
                <td class="p-2 align-top">
                    <select name="third_party_costs[${newIndex}][department]" ${isRequired} class="w-full rounded-md border border-gray-300 text-sm py-1.5 px-3 font-mono">
                        <option value="">Select</option>
                        <option value="creative">Creative</option>
                        <option value="digital">Digital</option>
                        <option value="play">Play</option>
                        <option value="tech">Tech</option>
                        <option value="PM">PM</option>
                        <option value="Corporate">Corporate</option>
                    </select>
                </td>
                <td class="p-2 align-top">
                    <input type="file" name="third_party_costs[${newIndex}][file]" ${isRequired} class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border file:border-gray-300 file:text-xs file:bg-brand-blue file:bg-opacity-10 file:text-brand-blue">
                </td>
                <td class="p-2 align-middle text-center">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($estimate->terms)
                @foreach(explode(', ', $estimate->terms) as $term)
                    addTerm("{!! addslashes($term) !!}");
                @endforeach
            @endif
            calculateAllRows();
            
            // Initialize auto-expand and row types for existing description boxes
            document.querySelectorAll('textarea[name*="[description]"]').forEach(textarea => {
                autoExpand(textarea);
                const row = textarea.closest('tr');
                const typeSelect = row.querySelector('select[name*="[type]"]');
                if (typeSelect) toggleRowType(typeSelect);
            });

            const checkedThirdParty = document.querySelector('input[name="third_party_cost"]:checked');
            if (checkedThirdParty) toggleThirdPartySection(checkedThirdParty.value);

            const checkedProforma = document.querySelector('input[name="proforma_invoice"]:checked');
            if (checkedProforma) toggleProformaFields(checkedProforma.value);

            Sortable.create(document.getElementById('items-body'), {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function () {
                    document.querySelectorAll('#items-body tr').forEach(function (tr, i) {
                        tr.querySelectorAll('[name]').forEach(function (el) {
                            el.name = el.name.replace(/items\[[^\]]+\]/, 'items[' + i + ']');
                        });
                        const posInput = tr.querySelector('input[name*="[position]"]');
                        if (posInput) posInput.value = i;
                    });
                }
            });

            const brandSelectInstance = new TomSelect('#brand_name_select', {
                create: true
            });

            // Customer Data for auto-populating fields
            @php
                $customersData = $customers->mapWithKeys(function($item) {
                     return [$item['id'] => [
                         'brand' => $item['brand'],
                         'attention' => $item['primary_contact_name'],
                         'designation' => $item['primary_contact_designation'],
                         'address' => $item['billing_address']
                     ]];
                 });
            @endphp
            const customersData = @json($customersData);

            const customerSelect = document.querySelector('select[name="customer_id"]');
            
            if (customerSelect) {
                customerSelect.addEventListener('change', function() {
                    const customerId = this.value;
                    const data = customersData[customerId];
                    
                    if (customerId && data) {
                        // Fill Brand
                        if (data.brand && brandSelectInstance) {
                            brandSelectInstance.addOption({value: data.brand, text: data.brand});
                            brandSelectInstance.setValue(data.brand);
                        } else if (brandSelectInstance) {
                            brandSelectInstance.clear();
                        }

                        // Fill Attention To
                        const attentionInput = document.querySelector('input[name="attention_to"]');
                        if (attentionInput) attentionInput.value = data.attention || '';

                        // Fill Designation
                        const designationInput = document.querySelector('input[name="designation"]');
                        if (designationInput) designationInput.value = data.designation || '';

                        // Fill Address
                        if (data.address) {
                            const lines = data.address.split(/\r?\n/);
                            document.querySelector('input[name="address_line_1"]').value = lines[0] || '';
                            document.querySelector('input[name="address_line_2"]').value = lines[1] || '';
                            document.querySelector('input[name="address_line_3"]').value = lines[2] || '';
                        } else {
                            document.querySelector('input[name="address_line_1"]').value = '';
                            document.querySelector('input[name="address_line_2"]').value = '';
                            document.querySelector('input[name="address_line_3"]').value = '';
                        }
                    } else {
                        if (brandSelectInstance) brandSelectInstance.clear();
                        document.querySelector('input[name="attention_to"]').value = '';
                        document.querySelector('input[name="designation"]').value = '';
                        document.querySelector('input[name="address_line_1"]').value = '';
                        document.querySelector('input[name="address_line_2"]').value = '';
                        document.querySelector('input[name="address_line_3"]').value = '';
                    }
                });
                
                // Trigger change immediately in case a customer is pre-selected
                if (customerSelect.value) {
                    customerSelect.dispatchEvent(new Event('change'));
                }

            }

            // Custom Form Validation
            const form = document.getElementById('estimate-form');
            function showError(input, message) {
                const parent = input.closest('td') || input.parentNode;
                if (!parent.classList.contains('relative')) parent.classList.add('relative');
                
                let errorDiv = parent.querySelector('.field-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error text-[10px] text-red-500 mt-0.5 absolute left-2';
                    parent.appendChild(errorDiv);
                }
                errorDiv.textContent = message;
                
                // If it is a description hidden input, put the border on the preview box
                if (input.classList.contains('description-hidden-input')) {
                    const previewContainer = parent.querySelector('.description-preview');
                    if (previewContainer) previewContainer.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                } else {
                    input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                }
            }

            function clearErrors(form) {
                form.querySelectorAll('.field-error').forEach(e => e.remove());
                form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500', 'ring-1', 'ring-red-500'));
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    clearErrors(this);
                    let isValid = true;
                    let firstErrorField = null;

                    // Check Brand Select (TomSelect)
                    if (typeof brandSelectInstance !== 'undefined' && brandSelectInstance && !brandSelectInstance.getValue()) {
                        isValid = false;
                        const control = brandSelectInstance.control;
                        control.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                        showError(control, 'Please select a brand');
                        if (!firstErrorField) firstErrorField = control;
                    }

                    // Check all data-required fields
                    this.querySelectorAll('[data-required="true"]').forEach(el => {
                        // Skip if it's the hidden select for brandSelectInstance handled above
                        if (el.id === 'brand_name_select') return;

                        let val = el.value;
                        // For Quill hidden fields, empty content might still have <p><br></p>
                        if (el.name.includes('[description]')) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = val;
                            val = tempDiv.textContent.trim();
                        }

                        if (!val || val.trim() === "" || (el.type === 'number' && parseFloat(val) < 0 && !el.name.includes('[unit_price]'))) {
                            isValid = false;
                            showError(el, 'Required');
                            if (!firstErrorField) {
                                // If hidden description input, scroll to the parent td
                                firstErrorField = el.classList.contains('description-hidden-input') ? (el.closest('td') || el.parentNode) : el;
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        if (firstErrorField) {
                            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            if (typeof firstErrorField.focus === 'function') firstErrorField.focus();
                        }
                    }
                });
            }

            @if(isset($readonly) && $readonly)
                document.querySelectorAll('form input, form select, form textarea').forEach(el => {
                    el.disabled = true;
                });
                // Special handling for TomSelect if necessary
                if (typeof brandSelectInstance !== 'undefined' && brandSelectInstance) {
                    brandSelectInstance.disable();
                }
            @endif
        });

        function toggleProformaFields(value) {
            const details = document.getElementById('proforma_details');
            if (!details) return;
            if (value === 'yes') {
                details.classList.remove('hidden');
            } else {
                details.classList.add('hidden');
            }
        }
    </script>
@endsection