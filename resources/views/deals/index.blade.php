@extends('layouts.app')

@section('header', 'Deals Pipeline')

@section('content')
    <style>
        .ts-wrapper {
            width: 100% !important;
        }

        

        .ts-wrapper .ts-control {
            border: 1px solid #d1d5db !important;
            /* border-gray-300 */
            border-radius: 0.5rem !important;
            /* rounded-lg */
            padding: 0.5rem 1rem !important;
            /* px-4 py-2 */
            box-shadow: none !important;
            font-size: 0.875rem !important;
            /* text-sm */
            line-height: 1.25rem !important;
            min-height: 42px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.2s !important;
            background-color: #fff !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: #8035ca !important;
            /* brand-purple */
            box-shadow: 0 0 0 2px rgba(128, 53, 202, 0.2) !important;
            outline: none !important;
        }

        .ts-wrapper.disabled .ts-control {
            background-color: #f3f4f6 !important;
            opacity: 1 !important;
        }

        .ts-dropdown {
            border-radius: 0.5rem !important;
            margin-top: 4px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e5e7eb !important;
        }

        .ts-dropdown .active {
            background-color: #8035ca !important;
            /* brand-purple */
            color: white !important;
        }

        .ts-control input {
            font-size: 0.875rem !important;
        }

        .cust-column {
            border-radius: 0.5rem !important;
        }

        .metric-contribution {
            display: none;
        }

        /* Compact styles for Tom Select inside the filter form */
        .filter-form .ts-wrapper {
            width: auto !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        .filter-form .ts-wrapper .ts-control {
            padding: 0.25rem 0.5rem !important; /* py-1 px-2 */
            min-height: 28px !important;
            font-size: 0.75rem !important; /* text-xs */
            border-radius: 0.375rem !important; /* rounded-md */
            line-height: 1.25rem !important;
            align-items: center !important;
        }
        .filter-form .ts-wrapper .ts-control input {
            font-size: 0.75rem !important;
        }
        .filter-form .ts-wrapper .ts-control .item {
            font-size: 0.70rem !important;
            padding: 0px 4px !important;
            margin: 1px !important;
            border-radius: 0.25rem !important;
            background-color: #f3f4f6 !important;
            border: 1px solid #e5e7eb !important;
            display: inline-flex !important;
            align-items: center !important;
        }
        .filter-form .ts-wrapper .ts-control .item .remove {
            font-size: 0.65rem !important;
            margin-left: 2px !important;
        }

        /* Transparent and autohiding scrollbar styling */
        #sticky-scrollbar {
            background: transparent !important;
            border-top: none !important;
            box-shadow: none !important;
            transition: opacity 0.35s ease-in-out;
            opacity: 0;
            pointer-events: none;
        }
        
        #sticky-scrollbar.scrolling {
            opacity: 1;
            pointer-events: auto;
        }

        #sticky-scrollbar::-webkit-scrollbar {
            height: 8px;
        }
        #sticky-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        #sticky-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(128, 53, 202, 0.4) !important; /* brand-purple thumb */
            border-radius: 9999px;
        }
        #sticky-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(128, 53, 202, 0.7) !important;
        }

        @media (max-width: 768px) {
            #sticky-scrollbar {
                display: none !important;
            }
        }
    </style>
    <div class="min-h-full flex flex-col">
        <!-- Top Stats -->
        @php
            $currency = $allDeals->pluck('currency')->unique()->count() === 1 ? $allDeals->first()->currency : 'LKR';
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-8 gap-4 mb-6">
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Project</h4>
                <div class="mt-2">
                    <p class="text-lg 2xl:text-base font-bold text-brand-purple mt-1 metric-revenue whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($totalProjectRevenue, 2) }}
                    </p>
                    <p class="text-lg 2xl:text-base font-bold text-brand-purple mt-1 metric-contribution whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($totalProjectContribution, 2) }}
                    </p>
                </div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Open Deals</h4>
                <p class="text-lg 2xl:text-base font-bold text-green-600 mt-1 whitespace-nowrap">
                    {{ $dealsByStage->flatten()->whereNotIn('stage', ['Rejected', 'Closed Won'])->count() }}
                </p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Weighted</h4>
                <div class="mt-2">
                    <p class="text-lg 2xl:text-base font-bold text-brand-blue mt-1 metric-revenue whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($weightedDealAmount, 2) }}
                    </p>
                    <p class="text-lg 2xl:text-base font-bold text-brand-blue mt-1 metric-contribution whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($weightedContributionAmount, 2) }}
                    </p>
                </div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Closed Won</h4>
                <div class="mt-2">
                    <p class="text-lg 2xl:text-base font-bold text-brand-pink mt-1 metric-revenue whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($approvedDealRevenue, 2) }}
                    </p>
                    <p class="text-lg 2xl:text-base font-bold text-brand-pink mt-1 metric-contribution whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($approvedDealContribution, 2) }}
                    </p>
                </div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">New (30d)</h4>
                <div class="mt-2">
                    <p class="text-lg 2xl:text-base font-bold text-brand-teal mt-1 metric-revenue whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($newDealRevenue, 2) }}
                    </p>
                    <p class="text-lg 2xl:text-base font-bold text-brand-teal mt-1 metric-contribution whitespace-nowrap">
                        <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($newDealContribution, 2) }}
                    </p>
                </div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Deal Age</h4>
                <p class="text-lg 2xl:text-base font-bold text-gray-700 mt-1 whitespace-nowrap">{{ $averageDealAge }}
                    <span class="text-xs font-normal opacity-75 ml-0.5">days</span>
                </p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Invoiced</h4>
                <p class="text-lg 2xl:text-base font-bold text-brand-blue mt-1 whitespace-nowrap">
                    <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($invoicedAmount, 2) }}
                </p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Payment Collected</h4>
                <p class="text-lg 2xl:text-base font-bold text-green-600 mt-1 whitespace-nowrap">
                    <span class="text-xs font-semibold opacity-75 mr-0.5">{{ $currency }}</span>{{ number_format($paymentCollected, 2) }}
                </p>
            </div>
        </div>


        <!-- Action Button -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">View Metrics As:</label>
                    <select id="metric-toggle" onchange="toggleMetrics(this.value)"
                        class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs font-bold py-1.5 px-3">
                        <option value="revenue">Revenue</option>
                        <option value="contribution">Contribution</option>
                    </select>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('deals.index') }}" method="GET" class="filter-form flex flex-wrap items-center gap-4 bg-gray-50 p-2 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-2">
                        <label for="start_date" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Start Month:</label>
                        <input type="month" name="start_date" id="start_date" value="{{ request('start_date') ? (strlen(request('start_date')) > 7 ? substr(request('start_date'), 0, 7) : request('start_date')) : '' }}"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border">
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="close_date" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Close Month:</label>
                        <input type="month" name="close_date" id="close_date" value="{{ request('close_date') ? (strlen(request('close_date')) > 7 ? substr(request('close_date'), 0, 7) : request('close_date')) : '' }}"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border">
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="created_date_type" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Deals Created Date:</label>
                        <select name="created_date_type" id="created_date_type" onchange="toggleCustomDateFields(this.value)"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border">
                            <option value="">All Time</option>
                            <option value="this_month" {{ request('created_date_type') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="previous_month" {{ request('created_date_type') == 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                            <option value="this_quarter" {{ request('created_date_type') == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="previous_quarter" {{ request('created_date_type') == 'previous_quarter' ? 'selected' : '' }}>Previous Quarter</option>
                            <option value="custom" {{ request('created_date_type') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <!-- Custom Date Fields (shown only when 'custom' is selected) -->
                    <div id="custom-date-container" class="{{ request('created_date_type') == 'custom' ? 'flex' : 'hidden' }} items-center gap-2">
                        <input type="date" name="created_from" id="created_from" value="{{ request('created_from') }}"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border" placeholder="From">
                        <span class="text-[10px] text-gray-500 font-bold uppercase">to</span>
                        <input type="date" name="created_to" id="created_to" value="{{ request('created_to') }}"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border" placeholder="To">
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="expected_close_date_type" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Deals Expected Closing Date:</label>
                        <select name="expected_close_date_type" id="expected_close_date_type" onchange="toggleCustomExpectedCloseFields(this.value)"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border">
                            <option value="">All Time</option>
                            <option value="this_month" {{ request('expected_close_date_type') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="previous_month" {{ request('expected_close_date_type') == 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                            <option value="next_month" {{ request('expected_close_date_type') == 'next_month' ? 'selected' : '' }}>Next Month</option>
                            <option value="this_quarter" {{ request('expected_close_date_type') == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="previous_quarter" {{ request('expected_close_date_type') == 'previous_quarter' ? 'selected' : '' }}>Previous Quarter</option>
                            <option value="next_quarter" {{ request('expected_close_date_type') == 'next_quarter' ? 'selected' : '' }}>Next Quarter</option>
                            <option value="custom" {{ request('expected_close_date_type') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <!-- Custom Expected Close Date Fields (shown only when 'custom' is selected) -->
                    <div id="custom-expected-close-container" class="{{ request('expected_close_date_type') == 'custom' ? 'flex' : 'hidden' }} items-center gap-2">
                        <input type="date" name="expected_close_from" id="expected_close_from" value="{{ request('expected_close_from') }}"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border" placeholder="From">
                        <span class="text-[10px] text-gray-500 font-bold uppercase">to</span>
                        <input type="date" name="expected_close_to" id="expected_close_to" value="{{ request('expected_close_to') }}"
                            class="rounded-md border-gray-300 shadow-sm focus:border-brand-purple focus:ring-brand-purple text-xs py-1 px-2 border" placeholder="To">
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="filter_user" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">User:</label>
                        <div class="min-w-[150px]">
                            <select name="filter_user[]" id="filter_user" multiple>
                                @foreach($filterableUsers as $filterUser)
                                    <option value="{{ $filterUser->id }}" {{ is_array(request('filter_user')) && in_array($filterUser->id, request('filter_user')) ? 'selected' : (request('filter_user') == $filterUser->id ? 'selected' : '') }}>
                                        {{ $filterUser->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="filter_department" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Dept:</label>
                        <div class="min-w-[130px]">
                            <select name="filter_department[]" id="filter_department" multiple>
                                @foreach($filterableDepartments as $dept)
                                    <option value="{{ $dept }}" {{ is_array(request('filter_department')) && in_array($dept, request('filter_department')) ? 'selected' : (request('filter_department') == $dept ? 'selected' : '') }}>
                                        {{ $dept }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="submit" class="px-3 py-1 bg-brand-purple text-white text-xs font-bold rounded-lg hover:bg-brand-blue transition-colors shadow-sm">
                            Filter
                        </button>
                        @if(request('start_date') || request('close_date') || request('created_date_type') || request('expected_close_date_type') || request('filter_user') || request('filter_department') || request('created_from') || request('created_to') || request('expected_close_from') || request('expected_close_to'))
                            <a href="{{ route('deals.index') }}" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <button onclick="document.getElementById('createDealModal').classList.remove('hidden')"
                class="bg-brand-pink hover:bg-brand-purple text-white font-bold py-2 px-4 rounded shadow transition-colors">
                <i class="fas fa-plus mr-2"></i> Create Deal
            </button>
        </div>



            <!-- Kanban Board -->
            <div class="overflow-x-auto overflow-y-visible" id="kanban-board-container">
                <div class="flex items-start space-x-4 pb-4" style="min-width: max-content;">
                    @foreach($stages as $stage)
                        <div class="w-80 flex-shrink-0 flex flex-col bg-gray-100 rounded-lg">
                            <div class="p-3 bg-gray-200 rounded-t-lg border-b border-gray-300 flex justify-between items-center">
                                <h3 class="font-bold text-gray-700 text-sm">{{ $stage }}</h3>
                                <div class="flex flex-col items-end">
                                    <span class="bg-gray-300 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full mb-1">
                                        {{ $dealsByStage->get($stage, collect())->count() }}
                                    </span>
                                    <div class="text-[10px] font-black text-gray-500 whitespace-nowrap">
                                        <span class="metric-revenue">LKR {{ number_format($dealsByStage->get($stage, collect())->sum(fn($d) => $d->dept_share_revenue ?? $d->revenue), 2) }}</span>
                                        <span class="metric-contribution">LKR {{ number_format($dealsByStage->get($stage, collect())->sum(fn($d) => $d->dept_share_contribution ?? $d->contribution), 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2 kanban-col" data-stage="{{ $stage }}">
                                @foreach($dealsByStage->get($stage, collect()) as $deal)
                                    <div class="bg-white p-3 rounded shadow-sm mb-3 cursor-move hover:shadow-md transition-shadow border-l-4 @if($stage === 'Rejected') border-red-500 @elseif($stage === 'Closed Won') border-green-500 @else border-brand-blue @endif"
                                        data-id="{{ $deal->id }}">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="font-bold text-gray-800 text-sm line-clamp-1 flex-1">{{ $deal->title }}</h4>
                                                @php 
                                                    $canEdit = $deal->canEdit(); 
                                                    $dealEstimates = $deal->estimates;
                                                    $hasEstimate = $dealEstimates->isNotEmpty(); 
                                                    $estimate = $dealEstimates->first();
                                                    $tempInvoice = $estimate ? $estimate->tempInvoice : null;
                                                    $taxInvoice = $dealEstimates->flatMap->invoices->where('is_proforma', 0)->first();
                                                @endphp
                                                <div class="flex items-center gap-1.5 ml-2">
                                                    {{-- Estimate/Invoice Link --}}
                                                    @if($taxInvoice)
                                                         <a href="{{ route('invoices.show', $taxInvoice->id) }}" target="_blank"
                                                             class="text-brand-pink hover:text-brand-purple transition-colors" 
                                                             title="View Invoice">
                                                             <i class="fas fa-file-invoice-dollar text-xs"></i>
                                                         </a>
                                                     @elseif($tempInvoice)
                                                         <a href="{{ route('temp-invoices.edit', $tempInvoice->id) }}"
                                                             class="text-orange-500 hover:text-orange-700 transition-colors" 
                                                             title="Process Temp Invoice">
                                                             <i class="fas fa-file-signature text-xs"></i>
                                                         </a>
                                                     @elseif($hasEstimate)
                                                         <div class="flex items-center space-x-1.5">
                                                            @if($canEdit)
                                                                <a href="{{ route('estimates.edit', $estimate->id) }}"
                                                                    class="text-purple-500 hover:text-purple-700 transition-colors" 
                                                                    title="Edit Estimate">
                                                                    <i class="fas fa-file-invoice text-xs"></i>
                                                                </a>
                                                            @else
                                                                <a href="{{ route('estimates.show', $estimate->id) }}" target="_blank"
                                                                    class="text-purple-500 hover:text-purple-700 transition-colors" 
                                                                    title="View Estimate">
                                                                    <i class="fas fa-file-invoice text-xs"></i>
                                                                </a>
                                                            @endif
                                                            @if($canEdit && in_array($estimate->status, ['accepted', 'ready_to_invoice']))
                                                                <button onclick="createTempInvoice({{ $deal->id }})"
                                                                    class="text-green-500 hover:text-green-700 transition-colors" 
                                                                    title="Create Invoice">
                                                                    <i class="fas fa-file-invoice-dollar text-xs"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @elseif($canEdit)
                                                        <button onclick="createEstimate({{ $deal->id }})"
                                                            class="text-green-500 hover:text-green-700 transition-colors" title="Create Estimate">
                                                            <i class="fas fa-file-invoice text-xs"></i>
                                                        </button>
                                                    @endif

                                                    {{-- Deal Edit/View Link (Hide in late stages) --}}
                                                    @if(!in_array($stage, ['Objection handling', 'Finalizing terms', 'Closed Won', 'Rejected']))
                                                        @if($canEdit)
                                                            <button data-deal="{{ base64_encode($deal->toJson()) }}" onclick="editDeal(JSON.parse(atob(this.dataset.deal)), false)"
                                                                class="text-blue-400 hover:text-blue-600 transition-colors" title="Edit Deal">
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                        @else
                                                            <button data-deal="{{ base64_encode($deal->toJson()) }}" onclick="editDeal(JSON.parse(atob(this.dataset.deal)), true)"
                                                                class="text-gray-400 hover:text-gray-600 transition-colors" title="View Deal Details">
                                                                <i class="fas fa-eye text-xs"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                        </div>
                                        <div class="flex flex-wrap gap-1 mb-2">
                                            @if($deal->job_number)
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded-full font-bold uppercase bg-brand-purple text-white">{{ $deal->job_number }}</span>
                                            @endif
                                            @if($stage === 'Rejected' && $deal->rejection_reason)
                                                <div
                                                    class="w-full mt-2 p-2 bg-red-50 text-red-700 text-[10px] rounded border border-red-100 italic">
                                                    <strong>Reason:</strong> {{ $deal->rejection_reason }}</div>
                                            @endif
                                            @if($deal->priority)
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded-full font-bold uppercase @if($deal->priority == 'High') bg-red-100 text-red-600 @elseif($deal->priority == 'Medium') bg-yellow-100 text-yellow-600 @else bg-blue-100 text-blue-600 @endif">{{ $deal->priority }}</span>
                                            @endif
                                            @if($deal->type)
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded-full font-bold uppercase bg-gray-100 text-gray-600">{{ $deal->type == 'New Business' ? 'New' : 'Existing' }}</span>
                                            @endif
                                            @if($deal->winning_percentage)
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded-full font-bold uppercase bg-emerald-100 text-emerald-600" title="Winning Probability">{{ $deal->winning_percentage }}%</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-gray-500 mb-2 flex items-center">
                                            <i class="fas fa-building mr-1"></i>
                                            <span
                                                class="truncate">{{ $deal->customer_name ?? $deal->customer->name ?? 'Unknown' }}</span>
                                        </p>
                                        <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-50">
                                            <div class="flex items-center">
                                                @if($deal->owner)
                                                    <div class="w-5 h-5 rounded-full bg-brand-purple flex items-center justify-center text-[10px] text-white font-bold mr-1"
                                                        title="Owner: {{ $deal->owner->name }}">
                                                        {{ strtoupper(substr($deal->owner->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span class="text-xs font-bold text-gray-900 metric-revenue">
                                                    {{ $deal->currency }} {{ number_format($deal->dept_share_revenue ?? $deal->revenue, 2) }}
                                                </span>
                                                <span class="text-xs font-bold text-gray-900 metric-contribution">
                                                    {{ $deal->currency }} {{ number_format($deal->dept_share_contribution ?? $deal->contribution, 2) }}
                                                </span>
                                            </div>
                                            <div class="flex -space-x-1.5 overflow-hidden py-1">
                                                @foreach($deal->teamMembers->take(4) as $member)
                                                    <div class="inline-block h-4 w-4 rounded-full ring-2 ring-white bg-slate-200 flex items-center justify-center text-[8px] font-black text-slate-600 uppercase"
                                                        title="{{ $member->name }}">
                                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                                    </div>
                                                @endforeach
                                                @if($deal->teamMembers->count() > 4)
                                                    <div
                                                        class="inline-block h-4 w-4 rounded-full ring-2 ring-white bg-slate-100 flex items-center justify-center text-[8px] font-bold text-slate-400">
                                                        +{{ $deal->teamMembers->count() - 4 }}
                                                    </div>
                                                @endif
                                            </div>
                                            @if($deal->close_date)
                                                <span
                                                    class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($deal->close_date)->format('M d') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Sticky Horizontal Scrollbar -->
            <div id="sticky-scrollbar" class="sticky bottom-0 left-0 right-0 w-full overflow-x-auto overflow-y-hidden z-30" style="display: none; height: 8px;">
                <div id="sticky-scrollbar-spacer" style="height: 1px;"></div>
            </div>

    </div>

    <!-- Create Deal Modal -->
    <div id="createDealModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border w-full max-w-2xl shadow-xl rounded-xl bg-white overflow-hidden">
            <div class="bg-brand-purple px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Create New Deal</h3>
                <button onclick="document.getElementById('createDealModal').classList.add('hidden')"
                    class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('deals.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deal Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title" required placeholder="e.g. Q4 Marketing Campaign"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1 flex items-center">
                                    Brand <span class="text-red-500">*</span> <i
                                        class="fas fa-info-circle ml-1 opacity-50 text-[10px]"></i>
                                </label>
                                <select name="customer_id" id="company_select" placeholder="Search Brand..." required
                                    class="cust-column">
                                    <option value="">Search Brand...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->brand ?: $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Pipeline <span
                                            class="text-red-500">*</span></label>
                                    <select name="pipeline" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                        <option value="Sales Pipeline">Sales Pipeline</option>
                                        <option value="Marketing Pipeline">Marketing Pipeline</option>
                                        <option value="Partnerships">Partnerships</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Deal Stage <span
                                            class="text-red-500">*</span></label>
                                    <select name="stage" id="create_stage" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                        @foreach($stages as $stageOption)
                                            <option value="{{ $stageOption }}">{{ $stageOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="create_rejection_reason_container" class="hidden mt-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Rejection Reason <span
                                        class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" id="create_rejection_reason" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none"
                                    placeholder="Enter reason for rejection..."></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Project Revenue <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" name="revenue" required placeholder="0.00"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Currency <span
                                            class="text-red-500">*</span></label>
                                    <select name="currency" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->code }}" {{ $currency->code == 'LKR' ? 'selected' : '' }}>
                                                {{ $currency->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Contribution <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" name="contribution" placeholder="0.00" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">


                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deal Owner</label>
                                <select name="senior_manager" id="create_senior_manager"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                    <option value="">-- Select Deal Owner --</option>
                                    @foreach($seniorManagers as $manager)
                                        <option value="{{ $manager->name }}" {{ (old('senior_manager') ?? (auth()->user()->role === 'HOD' ? auth()->user()->name : $currentSupervisor)) == $manager->name ? 'selected' : '' }}>
                                            {{ $manager->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deal Type</label>
                                <select name="type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                    <option value="New Business">New Business</option>
                                    <option value="Existing Business">Existing Business</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Priority</label>
                                <select name="priority"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Winning Percentage (%)</label>
                                <input type="number" name="winning_percentage" id="create_winning_percentage" readonly tabindex="-1"
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed outline-none select-none">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Close Date <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="close_date" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Project Split Section (Full Width) -->
                    <div class="bg-gray-50 -mx-6 p-6 border-y border-gray-100 mt-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-bold text-gray-700 flex items-center">
                                <i class="fas fa-users mr-2 text-brand-purple"></i> Project Split
                            </h4>
                            <button type="button" id="add-department-btn"
                                class="px-4 py-1.5 bg-brand-purple text-white text-xs font-bold rounded-lg hover:bg-brand-blue transition-all shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Add Department
                            </button>
                        </div>

                        <div id="department-allocations" class="space-y-3">
                            <!-- Dynamic department allocation rows will be added here -->
                        </div>

                        <!-- Hidden template for department row -->
                        <template id="department-row-template">
                            <div class="department-row flex flex-wrap md:flex-nowrap items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-100 w-full mb-2">
                                <div class="w-full md:w-3/12">
                                    <select class="department-select w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-white font-medium text-gray-700" required>
                                        <option value="">Department</option>
                                        @foreach(\App\Models\User::DEPARTMENT_HIERARCHY as $group => $departments)
                                            @foreach($departments as $key => $label)
                                                @if(!in_array($key, ['AM', 'BD']))
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        @endforeach
                                        <option value="Play">Play</option>
                                    </select>
                                </div>
                                <div class="w-[48%] md:w-2/12 relative">
                                    <input type="number" step="0.01" min="0" max="100" placeholder="Rev %" class="department-rev-percentage w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none pr-8 text-gray-700" required>
                                    <span class="absolute right-3 top-2.5 text-gray-400 text-xs font-medium">%</span>
                                </div>
                                <div class="w-[48%] md:w-2/12">
                                    <input type="number" step="0.01" min="0" placeholder="Revenue" class="department-rev-amount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none text-gray-700" required>
                                </div>
                                <div class="w-[48%] md:w-2/12 relative mt-2 md:mt-0">
                                    <input type="number" step="0.01" min="0" max="100" placeholder="Con %" class="department-con-percentage w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none pr-8 text-gray-700" required>
                                    <span class="absolute right-3 top-2.5 text-gray-400 text-xs font-medium">%</span>
                                </div>
                                <div class="w-[48%] md:w-2/12 mt-2 md:mt-0">
                                    <input type="number" step="0.01" min="0" placeholder="Contribution" class="department-con-amount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none text-gray-700" required>
                                </div>
                                <button type="button" class="remove-department-btn p-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all flex-shrink-0 mt-2 md:mt-0 shadow-sm" title="Remove Split">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3 bg-gray-50 -mx-6 -mb-6 p-6">
                    <button type="button" onclick="document.getElementById('createDealModal').classList.add('hidden')"
                        class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-8 py-2 bg-brand-pink text-white font-bold rounded-lg hover:bg-brand-purple transition-all shadow-md active:transform active:scale-95">
                        Create Deal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Deal Modal -->
    <div id="editDealModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border w-full max-w-2xl shadow-xl rounded-xl bg-white overflow-hidden">
            <div class="bg-brand-purple px-6 py-4 flex justify-between items-center">
                <h3 id="editDealModalTitle" class="text-xl font-bold text-white">Edit Deal</h3>
                <button onclick="document.getElementById('editDealModal').classList.add('hidden')"
                    class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="editDealForm" action="" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deal Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title" id="edit_title" required
                                    placeholder="e.g. Q4 Marketing Campaign"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple focus:border-transparent outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1 flex items-center">
                                    Brand <span class="text-red-500">*</span> <i
                                        class="fas fa-info-circle ml-1 opacity-50 text-[10px]"></i>
                                </label>
                                <select name="customer_id" id="edit_company_select" placeholder="Search Brand..." required
                                    class="cust-column">
                                    <option value="">Search Brand...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->brand ?: $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Pipeline <span
                                            class="text-red-500">*</span></label>
                                    <select name="pipeline" id="edit_pipeline" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                        <option value="Sales Pipeline">Sales Pipeline</option>
                                        <option value="Marketing Pipeline">Marketing Pipeline</option>
                                        <option value="Partnerships">Partnerships</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Deal Stage <span
                                            class="text-red-500">*</span></label>
                                    <select name="stage" id="edit_stage" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                        @foreach($stages as $stageOption)
                                            <option value="{{ $stageOption }}">{{ $stageOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="edit_rejection_reason_container" class="hidden mt-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Rejection Reason <span
                                        class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" id="edit_rejection_reason" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none"
                                    placeholder="Enter reason for rejection..."></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Project Revenue <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" name="revenue" id="edit_revenue" required
                                        placeholder="0.00"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Currency <span
                                            class="text-red-500">*</span></label>
                                    <select name="currency" id="edit_currency" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Contribution <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" name="contribution" id="edit_contribution" placeholder="0.00" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deal Owner</label>
                                <select name="senior_manager" id="edit_senior_manager"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                    <option value="">-- Select Deal Owner --</option>
                                    @foreach($seniorManagers as $manager)
                                        <option value="{{ $manager->name }}">{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deal Type</label>
                                <select name="type" id="edit_type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                    <option value="New Business">New Business</option>
                                    <option value="Existing Business">Existing Business</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Priority</label>
                                <select name="priority" id="edit_priority"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Winning Percentage (%)</label>
                                <input type="number" name="winning_percentage" id="edit_winning_percentage" readonly tabindex="-1"
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed outline-none select-none">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Close Date <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="close_date" id="edit_close_date" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Project Split Section (Full Width) -->
                    <div class="bg-gray-50 -mx-6 p-6 border-y border-gray-100 mt-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-bold text-gray-700 flex items-center">
                                <i class="fas fa-users mr-2 text-brand-purple"></i> Project Split
                            </h4>
                            <button type="button" id="edit-add-department-btn"
                                class="px-4 py-1.5 bg-brand-purple text-white text-xs font-bold rounded-lg hover:bg-brand-blue transition-all shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Add Department
                            </button>
                        </div>

                        <div id="edit-department-allocations" class="space-y-3">
                            <!-- Dynamic department allocation rows will be added here -->
                        </div>

                        <!-- Hidden template for edit department row -->
                        <template id="edit-department-row-template">
                            <div class="department-row flex flex-wrap md:flex-nowrap items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-100 w-full mb-2">
                                <div class="w-full md:w-3/12">
                                    <select class="department-select w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-white font-medium text-gray-700" required>
                                        <option value="">Department</option>
                                        @foreach(\App\Models\User::DEPARTMENT_HIERARCHY as $group => $departments)
                                            @foreach($departments as $key => $label)
                                                @if(!in_array($key, ['AM', 'BD']))
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        @endforeach
                                        <option value="Play">Play</option>
                                    </select>
                                </div>
                                <div class="w-[48%] md:w-2/12 relative">
                                    <input type="number" step="0.01" min="0" max="100" placeholder="Rev %" class="department-rev-percentage w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none pr-8 text-gray-700" required>
                                    <span class="absolute right-3 top-2.5 text-gray-400 text-xs font-medium">%</span>
                                </div>
                                <div class="w-[48%] md:w-2/12">
                                    <input type="number" step="0.01" min="0" placeholder="Revenue" class="department-rev-amount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none text-gray-700" required>
                                </div>
                                <div class="w-[48%] md:w-2/12 relative mt-2 md:mt-0">
                                    <input type="number" step="0.01" min="0" max="100" placeholder="Con %" class="department-con-percentage w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none pr-8 text-gray-700" required>
                                    <span class="absolute right-3 top-2.5 text-gray-400 text-xs font-medium">%</span>
                                </div>
                                <div class="w-[48%] md:w-2/12 mt-2 md:mt-0">
                                    <input type="number" step="0.01" min="0" placeholder="Contribution" class="department-con-amount w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none text-gray-700" required>
                                </div>
                                <button type="button" class="remove-department-btn p-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all flex-shrink-0 mt-2 md:mt-0 shadow-sm" title="Remove Split">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </template>
                        <input type="hidden" name="department_allocations_cleared" id="edit_department_allocations_cleared"
                            value="0">
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3 bg-gray-50 -mx-6 -mb-6 p-6">
                    <button type="button" onclick="document.getElementById('editDealModal').classList.add('hidden')"
                        class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                        Cancel
                    </button>
                    <button id="editDealSubmitBtn" type="submit"
                        class="px-8 py-2 bg-brand-pink text-white font-bold rounded-lg hover:bg-brand-purple transition-all shadow-md active:transform active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        function toggleCustomDateFields(val) {
            const container = document.getElementById('custom-date-container');
            if (!container) return;
            if (val === 'custom') {
                container.classList.remove('hidden');
                container.classList.add('flex');
            } else {
                container.classList.add('hidden');
                container.classList.remove('flex');
                const fromInput = document.getElementById('created_from');
                const toInput = document.getElementById('created_to');
                if (fromInput) fromInput.value = '';
                if (toInput) toInput.value = '';
            }
        }

        function toggleCustomExpectedCloseFields(val) {
            const container = document.getElementById('custom-expected-close-container');
            if (!container) return;
            if (val === 'custom') {
                container.classList.remove('hidden');
                container.classList.add('flex');
            } else {
                container.classList.add('hidden');
                container.classList.remove('flex');
                const fromInput = document.getElementById('expected_close_from');
                const toInput = document.getElementById('expected_close_to');
                if (fromInput) fromInput.value = '';
                if (toInput) toInput.value = '';
            }
        }

        function editDeal(deal, isReadOnly = false) {
            const form = document.getElementById('editDealForm');
            form.action = `/deals/${deal.id}`;

            document.getElementById('edit_title').value = deal.title;
            document.getElementById('edit_pipeline').value = deal.pipeline || 'Sales Pipeline';
            document.getElementById('edit_stage').value = deal.stage;
            document.getElementById('edit_revenue').value = deal.revenue;
            document.getElementById('edit_contribution').value = deal.contribution || '';
            document.getElementById('edit_currency').value = deal.currency;
            document.getElementById('edit_close_date').value = deal.close_date || '';
            document.getElementById('edit_senior_manager').value = deal.senior_manager || '';
            document.getElementById('edit_type').value = deal.type || 'New Business';
            document.getElementById('edit_priority').value = deal.priority || 'Medium';
            document.getElementById('edit_winning_percentage').value = deal.winning_percentage || '';
            document.getElementById('edit_company_select').tomselect.setValue(deal.customer_id || '');

            // Handle read-only mode UI
            const titleEl = document.getElementById('editDealModalTitle');
            const submitBtn = document.getElementById('editDealSubmitBtn');
            const addDeptBtn = document.getElementById('edit-add-department-btn');
            
            if (isReadOnly) {
                titleEl.textContent = 'View Deal Details';
                if (submitBtn) submitBtn.classList.add('hidden');
                if (addDeptBtn) addDeptBtn.classList.add('hidden');
            } else {
                titleEl.textContent = 'Edit Deal';
                if (submitBtn) submitBtn.classList.remove('hidden');
                if (addDeptBtn) addDeptBtn.classList.remove('hidden');
            }

            // Enable/Disable inputs
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.id === 'edit_winning_percentage') return; // Always readonly
                if (isReadOnly) {
                    input.setAttribute('disabled', 'disabled');
                    input.classList.add('bg-gray-50', 'cursor-not-allowed');
                } else {
                    input.removeAttribute('disabled');
                    input.classList.remove('bg-gray-50', 'cursor-not-allowed');
                }
            });

            // Handle TomSelect
            const ts = document.getElementById('edit_company_select').tomselect;
            if (isReadOnly) {
                ts.disable();
            } else {
                ts.enable();
            }

            // Handle rejection reason visibility
            const rejectionReasonContainer = document.getElementById('edit_rejection_reason_container');
            const rejectionReasonInput = document.getElementById('edit_rejection_reason');
            if (deal.stage === 'Rejected') {
                rejectionReasonContainer.classList.remove('hidden');
                rejectionReasonInput.value = deal.rejection_reason || '';
                if (!isReadOnly) rejectionReasonInput.setAttribute('required', 'required');
            } else {
                rejectionReasonContainer.classList.add('hidden');
                rejectionReasonInput.value = '';
                rejectionReasonInput.removeAttribute('required');
            }

            // Populate Department Split
            const editDeptContainer = document.getElementById('edit-department-allocations');
            const template = document.getElementById('edit-department-row-template');
            editDeptContainer.innerHTML = ''; // Clear existing

            if (deal.department_split) {
                try {
                    const allocations = typeof deal.department_split === 'string' ? JSON.parse(deal.department_split) : deal.department_split;
                    const items = Array.isArray(allocations) ? allocations : Object.values(allocations);

                    items.forEach((allocation, index) => {
                        if (allocation.department) {
                            const clone = template.content.cloneNode(true);
                            const row = clone.querySelector('.department-row');
                            const select = row.querySelector('.department-select');
                            const revPercentInput = row.querySelector('.department-rev-percentage');
                            const revAmountInput = row.querySelector('.department-rev-amount');
                            const conPercentInput = row.querySelector('.department-con-percentage');
                            const conAmountInput = row.querySelector('.department-con-amount');
                            const removeBtn = row.querySelector('.remove-department-btn');

                            select.value = allocation.department;
                            revPercentInput.value = allocation.revenue_percentage || allocation.percentage || '';
                            revAmountInput.value = allocation.revenue_amount || allocation.cost || '';
                            conPercentInput.value = allocation.contribution_percentage || '';
                            conAmountInput.value = allocation.contribution_amount || '';

                            select.name = `department_allocations[${index}][department]`;
                            revPercentInput.name = `department_allocations[${index}][revenue_percentage]`;
                            revAmountInput.name = `department_allocations[${index}][revenue_amount]`;
                            conPercentInput.name = `department_allocations[${index}][contribution_percentage]`;
                            conAmountInput.name = `department_allocations[${index}][contribution_amount]`;

                            if (isReadOnly) {
                                select.setAttribute('disabled', 'disabled');
                                revPercentInput.setAttribute('disabled', 'disabled');
                                revAmountInput.setAttribute('disabled', 'disabled');
                                conPercentInput.setAttribute('disabled', 'disabled');
                                conAmountInput.setAttribute('disabled', 'disabled');
                                removeBtn.classList.add('hidden');
                            } else {
                                removeBtn.addEventListener('click', function () {
                                    this.closest('.department-row').remove();
                                });
                            }
                            editDeptContainer.appendChild(clone);
                        }
                    });
                } catch (e) {
                    console.error('Error parsing department split:', e);
                }
            }

            document.getElementById('editDealModal').classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize TomSelect for User and Department filters
            if (document.getElementById('filter_user')) {
                new TomSelect('#filter_user', {
                    plugins: ['remove_button'],
                    placeholder: 'All Users',
                    create: false
                });
            }
            if (document.getElementById('filter_department')) {
                new TomSelect('#filter_department', {
                    plugins: ['remove_button'],
                    placeholder: 'All Departments',
                    create: false
                });
            }

            // ... existing sortable code ...
            const columns = document.querySelectorAll('.kanban-col');

            columns.forEach(col => {
                new Sortable(col, {
                    group: 'deals',
                    animation: 150,
                    ghostClass: 'bg-indigo-100',
                    onEnd: function (evt) {
                        const item = evt.item;
                        const fromStage = evt.from.getAttribute('data-stage');
                        const newStage = evt.to.getAttribute('data-stage');
                        const dealId = item.getAttribute('data-id');

                        if (fromStage === newStage) return;

                        // If moving to Rejected, prompt for reason directly
                        if (newStage === 'Rejected') {
                            // Revert visual move immediately, we will reload or append on success
                            evt.from.appendChild(item);
                            
                            Swal.fire({
                                title: 'Reject Deal',
                                text: 'Please enter the reason for rejection:',
                                input: 'textarea',
                                inputPlaceholder: 'Enter rejection reason...',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Reject Deal',
                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'You must enter a rejection reason!';
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch(`/deals/${dealId}/stage`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            stage: 'Rejected',
                                            rejection_reason: result.value
                                        })
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            return response.json().then(err => { throw err; });
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Status Updated',
                                            text: 'Deal rejected successfully.',
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Update Failed',
                                            text: error.message || 'An error occurred while rejecting the deal.'
                                        });
                                    });
                                }
                            });
                            return;
                        }

                        // Confirmation Prompt
                        Swal.fire({
                            title: 'Confirm Stage Change',
                            text: `Are you sure you want to move this deal to "${newStage}"?`,
                            showCancelButton: true,
                            confirmButtonColor: '#8035ca',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, move it!',
                            cancelButtonText: 'No, cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // API Call
                                fetch(`/deals/${dealId}/stage`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ stage: newStage })
                                })
                                    .then(response => {
                                        if (!response.ok) {
                                            return response.json().then(err => { throw err; });
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.redirect) {
                                            window.location.href = data.redirect;
                                            return;
                                        }
                                        if (data.job_number) {
                                            // Find or create job number badge
                                            let badgeContainer = item.querySelector('.flex.flex-wrap.gap-1.mb-2');
                                            let badge = Array.from(badgeContainer.children).find(el => el.textContent.trim() === data.job_number);

                                            if (!badge) {
                                                const badgeHtml = `<span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold uppercase bg-brand-purple text-white">${data.job_number}</span>`;
                                                badgeContainer.insertAdjacentHTML('afterbegin', badgeHtml);
                                            }
                                        }
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Status Updated',
                                            text: `Deal moved to ${newStage} successfully.`,
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        // Revert visual move
                                        evt.from.appendChild(item);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Update Failed',
                                            text: error.message || 'An error occurred while updating the deal stage.'
                                        });
                                    });
                            } else {
                                // Revert visual move
                                evt.from.appendChild(item);
                            }
                        });
                    }
                });
            });
        });

        // Department Allocation Functionality
        document.addEventListener('DOMContentLoaded', function () {
            const addDeptBtn = document.getElementById('add-department-btn');
            const deptContainer = document.getElementById('department-allocations');
            const template = document.getElementById('department-row-template');

            // Add department row
            addDeptBtn.addEventListener('click', function () {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.department-row');

                // Add remove functionality
                const removeBtn = clone.querySelector('.remove-department-btn');
                removeBtn.addEventListener('click', function () {
                    this.closest('.department-row').remove();
                });

                // Add name attributes for form submission
                const select = clone.querySelector('.department-select');
                const revPercentInput = clone.querySelector('.department-rev-percentage');
                const revAmountInput = clone.querySelector('.department-rev-amount');
                const conPercentInput = clone.querySelector('.department-con-percentage');
                const conAmountInput = clone.querySelector('.department-con-amount');
                const index = deptContainer.children.length;
                select.name = `department_allocations[${index}][department]`;
                revPercentInput.name = `department_allocations[${index}][revenue_percentage]`;
                revAmountInput.name = `department_allocations[${index}][revenue_amount]`;
                conPercentInput.name = `department_allocations[${index}][contribution_percentage]`;
                conAmountInput.name = `department_allocations[${index}][contribution_amount]`;

                deptContainer.appendChild(clone);
            });

            // Handle form submission for Create Deal
            const createForm = document.querySelector('#createDealModal form');
            if (createForm) {
                                createForm.addEventListener('submit', function (e) {
                    const rows = deptContainer.querySelectorAll('.department-row');
                    
                    // Validation: Sum of percentages and amounts should not exceed limit
                    let totalRevPercent = 0;
                    let totalConPercent = 0;
                    let totalRevAmt = 0;
                    let totalConAmt = 0;
                    const mainRev = parseFloat(document.querySelector('#createDealModal input[name="revenue"]').value) || 0;
                    const mainCon = parseFloat(document.querySelector('#createDealModal input[name="contribution"]').value) || 0;

                    rows.forEach(row => {
                        totalRevPercent += parseFloat(row.querySelector('.department-rev-percentage').value) || 0;
                        totalConPercent += parseFloat(row.querySelector('.department-con-percentage').value) || 0;
                        totalRevAmt += parseFloat(row.querySelector('.department-rev-amount').value) || 0;
                        totalConAmt += parseFloat(row.querySelector('.department-con-amount').value) || 0;
                    });

                    if (totalRevPercent > 100.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total Revenue Percentage cannot exceed 100% (Current: ' + totalRevPercent.toFixed(2) + '%)', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                    if (totalConPercent > 100.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total Contribution Percentage cannot exceed 100% (Current: ' + totalConPercent.toFixed(2) + '%)', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                    if (totalRevAmt > mainRev + 0.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total split revenue (LKR ' + totalRevAmt.toLocaleString() + ') cannot exceed project revenue (LKR ' + mainRev.toLocaleString() + ')', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                                        if (totalConAmt > mainCon + 0.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total split contribution (LKR ' + totalConAmt.toLocaleString() + ') cannot exceed total contribution (LKR ' + mainCon.toLocaleString() + ')', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }

                    if (mainCon > mainRev + 0.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Project Contribution (LKR ' + mainCon.toLocaleString() + ') cannot exceed Project Revenue (LKR ' + mainRev.toLocaleString() + ')', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                    rows.forEach((row, index) => {
                        const select = row.querySelector('.department-select');
                        const revPercentInput = row.querySelector('.department-rev-percentage');
                        const revAmountInput = row.querySelector('.department-rev-amount');
                        const conPercentInput = row.querySelector('.department-con-percentage');
                        const conAmountInput = row.querySelector('.department-con-amount');
                        select.name = `department_allocations[${index}][department]`;
                        revPercentInput.name = `department_allocations[${index}][revenue_percentage]`;
                        revAmountInput.name = `department_allocations[${index}][revenue_amount]`;
                        conPercentInput.name = `department_allocations[${index}][contribution_percentage]`;
                        conAmountInput.name = `department_allocations[${index}][contribution_amount]`;
                    });
                });
            }

            // Edit Deal Modal Functionality
            const editAddDeptBtn = document.getElementById('edit-add-department-btn');
            const editDeptContainer = document.getElementById('edit-department-allocations');
            const editTemplate = document.getElementById('edit-department-row-template');

            if (editAddDeptBtn) {
                editAddDeptBtn.addEventListener('click', function () {
                    const clone = editTemplate.content.cloneNode(true);

                    const removeBtn = clone.querySelector('.remove-department-btn');
                    removeBtn.addEventListener('click', function () {
                        this.closest('.department-row').remove();
                    });

                    const select = clone.querySelector('.department-select');
                    const userSelect = clone.querySelector('.user-select');
                    const revPercentInput = clone.querySelector('.department-rev-percentage');
                    const revAmountInput = clone.querySelector('.department-rev-amount');
                    const conPercentInput = clone.querySelector('.department-con-percentage');
                    const conAmountInput = clone.querySelector('.department-con-amount');
                    const index = editDeptContainer.children.length;
                    select.name = `department_allocations[${index}][department]`;
                    if (userSelect) userSelect.name = `department_allocations[${index}][user_name]`;
                    revPercentInput.name = `department_allocations[${index}][revenue_percentage]`;
                    revAmountInput.name = `department_allocations[${index}][revenue_amount]`;
                    conPercentInput.name = `department_allocations[${index}][contribution_percentage]`;
                    conAmountInput.name = `department_allocations[${index}][contribution_amount]`;

                    editDeptContainer.appendChild(clone);
                });
            }

            const editForm = document.getElementById('editDealForm');
            if (editForm) {
                                editForm.addEventListener('submit', function (e) {
                    const rows = editDeptContainer.querySelectorAll('.department-row');
                    
                    // Validation: Sum of percentages and amounts should not exceed limit
                    let totalRevPercent = 0;
                    let totalConPercent = 0;
                    let totalRevAmt = 0;
                    let totalConAmt = 0;
                    const mainRev = parseFloat(document.getElementById('edit_revenue').value) || 0;
                    const mainCon = parseFloat(document.getElementById('edit_contribution').value) || 0;

                    rows.forEach(row => {
                        totalRevPercent += parseFloat(row.querySelector('.department-rev-percentage').value) || 0;
                        totalConPercent += parseFloat(row.querySelector('.department-con-percentage').value) || 0;
                        totalRevAmt += parseFloat(row.querySelector('.department-rev-amount').value) || 0;
                        totalConAmt += parseFloat(row.querySelector('.department-con-amount').value) || 0;
                    });

                    if (totalRevPercent > 100.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total Revenue Percentage cannot exceed 100% (Current: ' + totalRevPercent.toFixed(2) + '%)', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                    if (totalConPercent > 100.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total Contribution Percentage cannot exceed 100% (Current: ' + totalConPercent.toFixed(2) + '%)', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                    if (totalRevAmt > mainRev + 0.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total split revenue (LKR ' + totalRevAmt.toLocaleString() + ') cannot exceed project revenue (LKR ' + mainRev.toLocaleString() + ')', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                                        if (totalConAmt > mainCon + 0.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Total split contribution (LKR ' + totalConAmt.toLocaleString() + ') cannot exceed total contribution (LKR ' + mainCon.toLocaleString() + ')', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }

                    if (mainCon > mainRev + 0.01) {
                        Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Project Contribution (LKR ' + mainCon.toLocaleString() + ') cannot exceed Project Revenue (LKR ' + mainRev.toLocaleString() + ')', confirmButtonColor: '#8035ca' });
                        e.preventDefault(); return false;
                    }
                    rows.forEach((row, index) => {
                        const select = row.querySelector('.department-select');
                        const revPercentInput = row.querySelector('.department-rev-percentage');
                        const revAmountInput = row.querySelector('.department-rev-amount');
                        const conPercentInput = row.querySelector('.department-con-percentage');
                        const conAmountInput = row.querySelector('.department-con-amount');
                        select.name = `department_allocations[${index}][department]`;
                        revPercentInput.name = `department_allocations[${index}][revenue_percentage]`;
                        revAmountInput.name = `department_allocations[${index}][revenue_amount]`;
                        conPercentInput.name = `department_allocations[${index}][contribution_percentage]`;
                        conAmountInput.name = `department_allocations[${index}][contribution_amount]`;
                    });

                    // Handle clearing
                    const clearedInput = document.getElementById('edit_department_allocations_cleared');
                    if (rows.length === 0) {
                        clearedInput.value = '1';
                    } else {
                        clearedInput.value = '0';
                    }
                });
            }

            function handleSplitCalculations(container, modalType) {
                container.addEventListener('input', function(e) {
                    const revInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="revenue"]') : document.getElementById('edit_revenue');
                    const conInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="contribution"]') : document.getElementById('edit_contribution');
                    const totalRev = parseFloat(revInput.value) || 0;
                    const totalCon = parseFloat(conInput.value) || 0;
                    const row = e.target.closest('.department-row');
                    if (!row) return;

                    const revPercent = row.querySelector('.department-rev-percentage');
                    const revAmt = row.querySelector('.department-rev-amount');
                    const conPercent = row.querySelector('.department-con-percentage');
                    const conAmt = row.querySelector('.department-con-amount');

                    if (e.target.classList.contains('department-rev-percentage')) {
                        const p = parseFloat(e.target.value) || 0;
                        if (totalRev > 0) revAmt.value = ((p / 100) * totalRev).toFixed(2);
                    } else if (e.target.classList.contains('department-rev-amount')) {
                        // Auto-summing: update total revenue based on all split amounts
                        let newTotalRev = 0;
                        container.querySelectorAll('.department-rev-amount').forEach(input => {
                            newTotalRev += parseFloat(input.value) || 0;
                        });
                        if (newTotalRev > 0) {
                            revInput.value = newTotalRev.toFixed(2);
                            // Recalculate percentages for all rows to match new total
                            container.querySelectorAll('.department-row').forEach(r => {
                                const rAmt = parseFloat(r.querySelector('.department-rev-amount').value) || 0;
                                const rPct = r.querySelector('.department-rev-percentage');
                                if (newTotalRev > 0) rPct.value = ((rAmt / newTotalRev) * 100).toFixed(1);
                            });
                        }
                    } else if (e.target.classList.contains('department-con-percentage')) {
                        const p = parseFloat(e.target.value) || 0;
                        if (totalCon > 0) conAmt.value = ((p / 100) * totalCon).toFixed(2);
                    } else if (e.target.classList.contains('department-con-amount')) {
                        // Auto-summing: update total contribution based on all split amounts
                        let newTotalCon = 0;
                        container.querySelectorAll('.department-con-amount').forEach(input => {
                            newTotalCon += parseFloat(input.value) || 0;
                        });
                        if (newTotalCon > 0) {
                            conInput.value = newTotalCon.toFixed(2);
                            // Recalculate percentages for all rows to match new total
                            container.querySelectorAll('.department-row').forEach(r => {
                                const rAmt = parseFloat(r.querySelector('.department-con-amount').value) || 0;
                                const rPct = r.querySelector('.department-con-percentage');
                                if (newTotalCon > 0) rPct.value = ((rAmt / newTotalCon) * 100).toFixed(1);
                            });
                        }
                    }
                });

                container.addEventListener('change', function(e) {
                    const revInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="revenue"]') : document.getElementById('edit_revenue');
                    const conInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="contribution"]') : document.getElementById('edit_contribution');
                    const totalRev = parseFloat(revInput.value) || 0;
                    const totalCon = parseFloat(conInput.value) || 0;

                    const rows = container.querySelectorAll('.department-row');
                    let currentRevP = 0, currentRevA = 0, currentConP = 0, currentConA = 0;
                    rows.forEach(r => {
                        currentRevP += parseFloat(r.querySelector('.department-rev-percentage').value) || 0;
                        currentRevA += parseFloat(r.querySelector('.department-rev-amount').value) || 0;
                        currentConP += parseFloat(r.querySelector('.department-con-percentage').value) || 0;
                        currentConA += parseFloat(r.querySelector('.department-con-amount').value) || 0;
                    });

                    if (e.target.classList.contains('department-rev-percentage') || e.target.classList.contains('department-rev-amount')) {
                        if (Math.round(currentRevP * 100) > 10000 || Math.round(currentRevA * 100) > Math.round(totalRev * 100) + 1) {
                            Swal.fire({ icon: 'warning', title: 'Limit Exceeded', text: 'Total Revenue Split exceeds project total. Please adjust.', confirmButtonColor: '#8035ca' });
                        }
                    } else if (e.target.classList.contains('department-con-percentage') || e.target.classList.contains('department-con-amount')) {
                         if (Math.round(currentConP * 100) > 10000 || Math.round(currentConA * 100) > Math.round(totalCon * 100) + 1) {
                            Swal.fire({ icon: 'warning', title: 'Limit Exceeded', text: 'Total Contribution Split exceeds project total. Please adjust.', confirmButtonColor: '#8035ca' });
                        }
                    }
                });

                // Also recalculate when main revenue/contribution changes
                const revInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="revenue"]') : document.getElementById('edit_revenue');
                const conInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="contribution"]') : document.getElementById('edit_contribution');
                
                function recalculateAll() {
                    const revInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="revenue"]') : document.getElementById('edit_revenue');
                    const conInput = modalType === 'create' ? document.querySelector('#createDealModal input[name="contribution"]') : document.getElementById('edit_contribution');
                    const totalRev = parseFloat(revInput.value) || 0;
                    const totalCon = parseFloat(conInput.value) || 0;
                    const rows = container.querySelectorAll('.department-row');
                    rows.forEach(row => {
                        const revPercent = row.querySelector('.department-rev-percentage');
                        const revAmt = row.querySelector('.department-rev-amount');
                        const conPercent = row.querySelector('.department-con-percentage');
                        const conAmt = row.querySelector('.department-con-amount');

                        if (revPercent.value) {
                            revAmt.value = ((parseFloat(revPercent.value) / 100) * totalRev).toFixed(2);
                        }
                        if (conPercent.value) {
                            conAmt.value = ((parseFloat(conPercent.value) / 100) * totalCon).toFixed(2);
                        }
                    });
                }
                
                if (revInput) revInput.addEventListener('input', recalculateAll);
                if (conInput) conInput.addEventListener('input', recalculateAll);
            }

            handleSplitCalculations(document.getElementById('department-allocations'), 'create');
            handleSplitCalculations(document.getElementById('edit-department-allocations'), 'edit');

            // Added validation for main Revenue vs Contribution
            function setupMainMetricsValidation(revId, conId, modalSelector) {
                const revInput = revId ? document.getElementById(revId) : document.querySelector(modalSelector + ' input[name="revenue"]');
                const conInput = conId ? document.getElementById(conId) : document.querySelector(modalSelector + ' input[name="contribution"]');

                if (!revInput || !conInput) return;

                function validateMetrics() {
                    const rev = parseFloat(revInput.value) || 0;
                    const con = parseFloat(conInput.value) || 0;
                    if (con > rev) {
                        conInput.value = rev;
                    }
                }

                revInput.addEventListener('input', validateMetrics);
                conInput.addEventListener('input', validateMetrics);
            }

            setupMainMetricsValidation(null, null, '#createDealModal');
            setupMainMetricsValidation('edit_revenue', 'edit_contribution', null);

        });
        // Initialize Tom Select
        document.addEventListener('DOMContentLoaded', function () {
            // Shared stage probabilities
            const stageProbs = {
                'Planned to Meet': 10,
                'Introductory meeting': 10,
                'Brief Stage': 20,
                'Working on pitch': 40,
                'Pitched': 50,
                'Objection handling': 80,
                'Finalizing terms': 90,
                'Rejected': 0,
                'Closed Won': 100
            };

            // Handle edit stage change for rejection reason
            const editStageSelect = document.getElementById('edit_stage');
            const editRejectionReasonContainer = document.getElementById('edit_rejection_reason_container');
            const editRejectionReasonInput = document.getElementById('edit_rejection_reason');
            const editWinningPercentageInput = document.getElementById('edit_winning_percentage');

            // Handle create stage change for rejection reason
            const createStageSelect = document.getElementById('create_stage');
            const createRejectionReasonContainer = document.getElementById('create_rejection_reason_container');
            const createRejectionReasonInput = document.getElementById('create_rejection_reason');
            const createWinningPercentageInput = document.getElementById('create_winning_percentage');

            if (createStageSelect) {
                // Initialize default
                if (createWinningPercentageInput && createStageSelect.value) {
                     createWinningPercentageInput.value = stageProbs[createStageSelect.value] || 0;
                }
                
                createStageSelect.addEventListener('change', function () {
                    
                    if (createWinningPercentageInput) {
                        createWinningPercentageInput.value = stageProbs[this.value] || 0;
                    }

                    if (this.value === 'Rejected') {
                        createRejectionReasonContainer.classList.remove('hidden');
                        createRejectionReasonInput.setAttribute('required', 'required');
                    } else {
                        createRejectionReasonContainer.classList.add('hidden');
                        createRejectionReasonInput.removeAttribute('required');
                        createRejectionReasonInput.value = '';
                    }
                });
            }

            if (editStageSelect) {
                editStageSelect.addEventListener('change', function () {
                    
                    if (editWinningPercentageInput) {
                        editWinningPercentageInput.value = stageProbs[this.value] || 0;
                    }

                    if (this.value === 'Rejected') {
                        editRejectionReasonContainer.classList.remove('hidden');
                        editRejectionReasonInput.setAttribute('required', 'required');
                    } else {
                        editRejectionReasonContainer.classList.add('hidden');
                        editRejectionReasonInput.removeAttribute('required');
                        editRejectionReasonInput.value = '';
                    }
                });
            }

            new TomSelect('#company_select', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results p-4 text-center border-t border-gray-100 bg-gray-50">' +
                               '<div class="mb-2 text-red-500 font-bold">' +
                               '<i class="fas fa-exclamation-circle mr-2"></i>Brand Not Found' +
                               '</div>' +
                               '<p class="text-[11px] text-gray-500 mb-3 px-2 leading-relaxed">"' + escape(data.input) + '" is not in the brand list. Please add a new customer to register this brand.</p>' +
                               '<a href="{{ route('customers.create') }}" target="_blank" class="inline-flex items-center justify-center bg-brand-purple text-white text-[10px] font-bold py-1.5 px-3 rounded hover:bg-brand-blue transition-all">' +
                               '<i class="fas fa-plus-circle mr-1.5"></i>Add New Brand' +
                               '</a>' +
                               '</div>';
                    }
                }
            });

            new TomSelect('#edit_company_select', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results p-4 text-center border-t border-gray-100 bg-gray-50">' +
                               '<div class="mb-2 text-red-500 font-bold">' +
                               '<i class="fas fa-exclamation-circle mr-2"></i>Brand Not Found' +
                               '</div>' +
                               '<p class="text-[11px] text-gray-500 mb-3 px-2 leading-relaxed">"' + escape(data.input) + '" is not in the brand list. Please add a new customer to register this brand.</p>' +
                               '<a href="{{ route('customers.create') }}" target="_blank" class="inline-flex items-center justify-center bg-brand-purple text-white text-[10px] font-bold py-1.5 px-3 rounded hover:bg-brand-blue transition-all">' +
                               '<i class="fas fa-plus-circle mr-1.5"></i>Add New Brand' +
                               '</a>' +
                               '</div>';
                    }
                }
            });
        });

        // Auto-select Deal Owner based on Assigned User
        document.addEventListener('DOMContentLoaded', function () {
            function setupSeniorManagerSync(userIdSelectId, seniorManagerSelectId) {
                // syncing logic removed since edit_user_id no longer exists
            }
        });

        // Auto-open deal modal if deal_id is present in URL
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const dealId = urlParams.get('deal_id');
            if (dealId) {
                setTimeout(() => {
                    const dealCard = document.querySelector(`.kanban-col [data-id="${dealId}"]`);
                    if (dealCard) {
                        const editBtn = dealCard.querySelector('button[onclick*="editDeal"]');
                        if (editBtn) editBtn.click();
                    }
                }, 500);
            }
        });

        function createEstimate(dealId) {
            Swal.fire({
                title: 'Creating Estimate...',
                text: 'Please wait while we set up the estimate draft.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/deals/${dealId}/create-estimate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    Swal.fire('Error', data.message || 'Failed to create estimate', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            });
        }

        function createTempInvoice(dealId) {
            Swal.fire({
                title: 'Creating Invoice...',
                text: 'Please wait while we set up the temporary invoice.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/deals/${dealId}/create-invoice`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    Swal.fire('Error', data.message || 'Failed to create temporary invoice', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            });
        }

        function toggleMetrics(type) {
            const revenueMetrics = document.querySelectorAll('.metric-revenue');
            const contributionMetrics = document.querySelectorAll('.metric-contribution');
            
            if (type === 'revenue') {
                revenueMetrics.forEach(el => el.style.display = 'block');
                contributionMetrics.forEach(el => el.style.display = 'none');
            } else {
                revenueMetrics.forEach(el => el.style.display = 'none');
                contributionMetrics.forEach(el => el.style.display = 'block');
            }
        }

        // Sticky Horizontal Scrollbar implementation
        document.addEventListener('DOMContentLoaded', function() {
            const board = document.getElementById('kanban-board-container');
            const stickyScroll = document.getElementById('sticky-scrollbar');
            const stickySpacer = document.getElementById('sticky-scrollbar-spacer');
            const scrollContainer = document.querySelector('main');
            
            if (!board || !stickyScroll || !stickySpacer) return;

            let isTicking = false;

            function updateStickyScrollbar() {
                // Disable sticky scrollbar on mobile/touch viewports to prevent scroll jitter
                if (window.innerWidth <= 768 || 'ontouchstart' in window) {
                    stickyScroll.style.display = 'none';
                    return;
                }

                if (!isTicking) {
                    requestAnimationFrame(function() {
                        const rect = board.getBoundingClientRect();
                        const windowHeight = window.innerHeight;
                        const scrollWidth = board.scrollWidth;
                        const clientWidth = board.clientWidth;

                        // Check if the board has horizontal overflow
                        const hasOverflow = scrollWidth > clientWidth;
                        
                        // Check if the bottom of the board is already visible in the viewport
                        const bottomVisible = rect.bottom <= (windowHeight - 24);

                        if (hasOverflow && !bottomVisible) {
                            stickyScroll.style.display = 'block';
                            stickySpacer.style.width = scrollWidth + 'px';
                            stickyScroll.scrollLeft = board.scrollLeft;
                        } else {
                            stickyScroll.style.display = 'none';
                        }
                        isTicking = false;
                    });
                    isTicking = true;
                }
            }

            // Sync scroll positions and autohide logic
            let isSyncingBoard = false;
            let isSyncingSticky = false;
            let scrollTimeout;

            function showScrollbar() {
                stickyScroll.classList.add('scrolling');
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    stickyScroll.classList.remove('scrolling');
                }, 1500); // fade out after 1.5 seconds of inactivity
            }

            board.addEventListener('scroll', function() {
                if (window.innerWidth <= 768) return;
                showScrollbar();
                if (isSyncingSticky) {
                    isSyncingSticky = false;
                    return;
                }
                isSyncingBoard = true;
                stickyScroll.scrollLeft = board.scrollLeft;
            });

            stickyScroll.addEventListener('scroll', function() {
                if (window.innerWidth <= 768) return;
                showScrollbar();
                if (isSyncingBoard) {
                    isSyncingBoard = false;
                    return;
                }
                isSyncingSticky = true;
                board.scrollLeft = stickyScroll.scrollLeft;
            });

            // Update scroll state on load and various window events
            updateStickyScrollbar();
            window.addEventListener('resize', updateStickyScrollbar);
            if (scrollContainer) {
                scrollContainer.addEventListener('scroll', updateStickyScrollbar, { passive: true });
                
                // Show scrollbar when mouse is near the bottom of the viewport
                scrollContainer.addEventListener('mousemove', function(e) {
                    if (window.innerWidth <= 768) return;
                    const rect = scrollContainer.getBoundingClientRect();
                    // If mouse is within 40px of the bottom of the main content view
                    if (e.clientY >= (rect.bottom - 40)) {
                        showScrollbar();
                    }
                });
            }

            // Monitor cards list changes and board mutations to dynamically adjust widths
            const observer = new MutationObserver(updateStickyScrollbar);
            observer.observe(board, { childList: true, subtree: true });
        });
    </script>
@endsection