@extends('layouts.app')

@section('header', 'Business Intelligence Reports')

@section('content')
<div class="flex flex-col space-y-8" x-data="{ 
    columns: $persist({
        deals: ['date', 'title', 'customer', 'owner', 'type', 'stage', 'revenue'],
        invoices: ['date', 'invoice_no', 'customer', 'deal', 'status', 'amount']
    }).as('report_columns'),
    showPicker: false,
    isColumnVisible(type, col) {
        return this.columns[type].includes(col);
    },
    toggleColumn(type, col) {
        if (this.isColumnVisible(type, col)) {
            this.columns[type] = this.columns[type].filter(c => c !== col);
        } else {
            this.columns[type].push(col);
        }
    }
}">
    <!-- Professional Filter Grid (Matching Reference Style) -->
    <div class="bg-white p-4 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
        <form action="{{ route('reports.index') }}" method="GET" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 items-end">
                <!-- Column 1: Period -->
                <div class="space-y-2 col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Analysis Period</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[9px] text-gray-400 block mb-1">From</span>
                            <input type="month" name="start_date" value="{{ $startDate ? $startDate->format('Y-m') : '' }}" 
                                class="w-full px-2.5 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-gray-50/30">
                        </div>
                        <div>
                            <span class="text-[9px] text-gray-400 block mb-1">To</span>
                            <input type="month" name="end_date" value="{{ $endDate ? $endDate->format('Y-m') : '' }}" 
                                class="w-full px-2.5 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-gray-50/30">
                        </div>
                    </div>
                </div>

                <!-- Column 2: Department (Only for Admins/Management) -->
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Management'))
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Department / Category</label>
                    <select name="department" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-gray-50/30 h-[38px]">
                        <option value="">All Departments</option>
                        @foreach(['Creative', 'Digital', 'Tech', 'PM', 'Corporate'] as $d)
                            <option value="{{ $d }}" {{ $department == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Column 3: Customer & Stage -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Stage / Customer</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select name="stage" class="w-full px-2.5 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-gray-50/30 h-[38px]">
                            <option value="">All Stages</option>
                            @foreach($stages as $s)
                                <option value="{{ $s }}" {{ $stageFilter == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="customer_name" value="{{ $customerName }}" placeholder="Customer..."
                            class="w-full px-2.5 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-purple outline-none bg-gray-50/30 h-[38px]">
                    </div>
                </div>

                <!-- Column 4: Actions -->
                <div class="flex flex-wrap sm:flex-nowrap gap-2">
                    <button type="submit" class="flex-1 px-3 py-2 bg-brand-blue text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-brand-purple transition-all shadow-md active:scale-95 h-[38px]">
                        <i class="fas fa-sync-alt mr-1"></i> Update
                    </button>
                    <a href="{{ route('reports.export', array_merge(request()->all(), ['type' => 'deals'])) }}" 
                       class="px-3 py-2 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-emerald-700 transition-all text-center flex items-center justify-center h-[38px] shadow-md border border-emerald-500">
                        <i class="fas fa-file-csv mr-1"></i> Export Deals
                    </a>
                    <a href="{{ route('reports.index') }}" class="px-3 py-2 bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-gray-200 transition-all text-center flex items-center justify-center h-[38px]">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Quick Insights (Added for HOD/Managers) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_type' => 'pending'])) }}" 
           class="group p-6 rounded-2xl border-2 transition-all duration-300 {{ $reportType === 'pending' ? 'bg-indigo-50 border-indigo-200 shadow-sm' : 'bg-white border-transparent hover:border-indigo-100 hover:shadow-md' }}">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl {{ $reportType === 'pending' ? 'bg-indigo-500 text-white' : 'bg-indigo-50 text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white' }} transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl font-black {{ $reportType === 'pending' ? 'text-indigo-600' : 'text-slate-800' }}">{{ number_format($pendingCount) }}</span>
            </div>
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Pending Deals</h3>
            <p class="text-xs text-gray-500 mt-1">Deals requiring follow-up or negotiation.</p>
        </a>

        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_type' => 'complete'])) }}" 
           class="group p-6 rounded-2xl border-2 transition-all duration-300 {{ $reportType === 'complete' ? 'bg-emerald-50 border-emerald-200 shadow-sm' : 'bg-white border-transparent hover:border-emerald-100 hover:shadow-md' }}">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl {{ $reportType === 'complete' ? 'bg-emerald-500 text-white' : 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white' }} transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl font-black {{ $reportType === 'complete' ? 'text-emerald-600' : 'text-slate-800' }}">{{ number_format($completeCount) }}</span>
            </div>
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Complete Deals</h3>
            <p class="text-xs text-gray-500 mt-1">Successfully closed won projects.</p>
        </a>

        <a href="{{ route('reports.index', array_merge(request()->query(), ['report_type' => 'deadlines'])) }}" 
           class="group p-6 rounded-2xl border-2 transition-all duration-300 {{ $reportType === 'deadlines' ? 'bg-amber-50 border-amber-200 shadow-sm' : 'bg-white border-transparent hover:border-amber-100 hover:shadow-md' }}">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl {{ $reportType === 'deadlines' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-500 group-hover:bg-amber-500 group-hover:text-white' }} transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-2xl font-black {{ $reportType === 'deadlines' ? 'text-amber-600' : 'text-slate-800' }}">{{ number_format($deadlineCount) }}</span>
            </div>
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Project Deadlines</h3>
            <p class="text-xs text-gray-500 mt-1">Upcoming project estimated closing dates.</p>
        </a>
    </div>

    <!-- Original Sections -->


    <!-- Removed Detailed Report section as requested -->

    <!-- Detailed Report Section (Invoices) -->
    @include('reports.partials.detailed_report')

    <!-- Charts & Detailed Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Trend -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h4 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-wider flex items-center">
                <i class="fas fa-chart-line mr-2 text-brand-purple"></i> Revenue Trend
            </h4>
            <div class="h-[250px] flex items-center justify-center bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                @if($dailyRevenue->count() > 0)
                    <div class="text-center group p-8">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mx-auto mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-check text-green-500"></i>
                        </div>
                        <p class="text-slate-800 text-xs font-bold">{{ $dailyRevenue->count() }} days of data available.</p>
                        <p class="text-gray-400 text-[10px] mt-1 italic">Visual charts will be rendered here.</p>
                    </div>
                @else
                    <div class="text-center group p-8">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mx-auto mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-database text-gray-300"></i>
                        </div>
                        <p class="text-gray-400 text-xs italic">No daily revenue data for this range.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Deals by Stage -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h4 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-wider flex items-center">
                <i class="fas fa-chart-pie mr-2 text-brand-blue"></i> Deals by Stage
            </h4>
            <div class="space-y-4">
                @forelse($dealsByStage as $stage)
                    <div class="relative pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-[10px] font-black py-1 px-2 uppercase rounded-full text-brand-blue bg-blue-50">
                                    {{ $stage->stage }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-800">
                                    {{ $dealsByStage->pluck('currency')->unique()->count() === 1 ? ($dealsByStage->first()->currency ?? 'LKR') : 'LKR' }} {{ number_format($stage->total, 2) }}
                                </span>
                                <span class="text-[10px] text-gray-400 ml-1">({{ $stage->count }} deals)</span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 text-xs flex rounded-full bg-gray-100">
                            @php 
                                $percentage = $totalDealRevenue > 0 ? ($stage->total / $totalDealRevenue) * 100 : 0;
                            @endphp
                            <div style="width:{{ $percentage }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-brand-blue transition-all duration-1000"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-xs italic text-center py-10">No deal data found for this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Original Breakdown Table -->
</div>
@endsection
