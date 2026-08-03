@extends('layouts.app')

@section('header', '')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<style>
    :root {
        --primary: #2563eb;
        --primary-light: #eff6ff;
        --secondary: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --background: #f8fafc;
        --card-bg: rgba(255, 255, 255, 0.9);
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }

    body {
        background-color: var(--background);
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--text-main);
    }

    .dashboard-container {
        max-width: 1550px;
        margin: 0 auto;
        padding: 24px;
        min-height: 100vh;
    }

    /* Glassmorphism Card Style */
    .glass-card {
        background: var(--card-bg);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 16px;
        box-shadow: var(--glass-shadow);
        padding: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.12);
    }

    /* Filters Section */
    .filters-bar {
        background: white;
        border-radius: 12px;
        padding: 16px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-end;
        border: 1px solid var(--border-color);
    }

    .filter-group {
        flex: 1;
        min-width: 140px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        display: block;
    }

    .filter-select {
        width: 100%;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        color: var(--text-main);
        background-color: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }

    .filter-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* KPI Cards */
    .kpi-card {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .kpi-title {
        font-size: 13px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    /* Chart Elements */
    .chart-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-title i {
        color: var(--primary);
    }

    /* Table Styling */
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table th {
        background: var(--primary-light);
        color: var(--primary);
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        padding: 12px;
        text-align: left;
    }

    .custom-table td {
        padding: 12px;
        font-size: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .custom-table tr:last-child td {
        border-bottom: none;
    }

    .custom-table tr:hover td {
        background-color: var(--primary-light);
    }

    /* Tab Styling */
    .dashboard-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 2px;
    }

    .tab-btn {
        padding: 10px 24px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 8px 8px 0 0;
    }

    .tab-btn:hover {
        background: var(--primary-light);
        color: var(--primary);
    }

    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: var(--primary-light);
    }

    .tab-content {
        transition: opacity 0.3s ease;
    }

    .tab-content.hidden {
        display: none;
        opacity: 0;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
        animation: fadeIn 0.5s ease onwards;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 12px 6px;
        }
        .glass-card {
            padding: 14px;
            border-radius: 12px;
        }
        .filters-bar {
            padding: 12px;
            gap: 12px;
        }
        .filter-group {
            min-width: 100%;
        }
        .dashboard-tabs {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .tab-btn {
            padding: 8px 16px;
            font-size: 13px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container relative">
    

    <div class="dashboard-tabs">
        <button class="tab-btn active" onclick="switchTab('overview')">Overview</button>
        @if(auth()->user()->role === 'Super Admin')
            <button class="tab-btn" onclick="switchTab('target-type')">Target Type</button>
        @endif
    </div>

    <!-- Overview Tab Content -->
    <div id="overview-tab" class="tab-content transition-all duration-300">
        <!-- Filters & KPI Header -->
        <div class="flex flex-col xl:flex-row gap-6 mb-6 items-stretch">
            <div class="flex-1 flex flex-col md:flex-row gap-4 items-center bg-white py-4 px-6 rounded-xl border border-slate-100 shadow-sm">
                <form id="filterForm" action="{{ route('dashboard') }}" method="GET" class="filters-bar flex-1 mb-0 p-0 shadow-none border-none self-center h-full">
                <div class="filter-group">
                    <label class="filter-label">Month</label>
                    <select name="month" class="filter-select" onchange="this.form.submit()">
                        <option value="all">All Months</option>
                        @foreach($months as $val => $lbl)
                            <option value="{{ $val }}" {{ $month == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->role === 'Manager')
                <div class="filter-group">
                    <label class="filter-label">Brand</label>
                    <select name="brand" class="filter-select" onchange="this.form.submit()">
                        <option value="all">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b }}" {{ $brandFilter == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Customer</label>
                    <select name="customer" class="filter-select" onchange="this.form.submit()">
                        <option value="all">All Customers</option>
                        @foreach($managerCustomers as $id => $name)
                            <option value="{{ $id }}" {{ $customerFilter == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="filter-group">
                    <label class="filter-label">Brand</label>
                    <select name="brand" class="filter-select" onchange="this.form.submit()">
                        <option value="all">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b }}" {{ $brandFilter == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Manager</label>
                    <select name="manager" class="filter-select" onchange="this.form.submit()">
                        @if(count($managers) > 1)
                            <option value="all">All Managers</option>
                        @endif
                        @foreach($managers as $id => $name)
                            <option value="{{ $id }}" {{ $managerFilter == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->role !== 'HOD')
                <div class="filter-group">
                    <label class="filter-label">Department</label>
                    <select name="department" class="filter-select" onchange="this.form.submit()">
                        <option value="all">All Depts</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ $departmentFilter == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="filter-group">
                    <label class="filter-label">Stages</label>
                    <select name="stage" class="filter-select" onchange="this.form.submit()">
                        <option value="all">All Stages</option>
                        @foreach($stages as $s)
                            <option value="{{ $s }}" {{ $stageFilter == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </form>

            <a href="{{ route('dashboard.export', request()->all()) }}" 
               class="btn bg-slate-900 hover:bg-black text-white px-5 py-2.5 rounded-lg font-bold text-xs flex items-center gap-2 transition-all shadow-sm shrink-0">
                <i class="fas fa-file-csv text-slate-400"></i>
                Export CSV
            </a>
        </div>

            @if(auth()->user()->role === 'Manager')
            </div>
            <div class="flex gap-4 min-w-[320px] w-full mt-4 flex-wrap">
                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-[#2563eb]">
                    <div class="kpi-title">My Target</div>
                    <div class="kpi-value text-2xl">
                        <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                        {{ number_format($userTarget / 1000000, 2) }}M
                    </div>
                </div>

                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-slate-500">
                    <div class="kpi-title">Dept. Target ({{ auth()->user()->department }})</div>
                    <div class="kpi-value text-2xl">
                        <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                        {{ number_format($deptTarget / 1000000, 2) }}M
                    </div>
                </div>

                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-[#10b981]">
                    <div class="kpi-title">Achieved Target</div>
                    @php $achievedPct = $userTarget > 0 ? min(100, ($totalContribution / $userTarget) * 100) : 0; @endphp
                    <div class="flex flex-col items-center w-full">
                        <div class="kpi-value text-2xl mb-1">
                            <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                            {{ number_format($totalContribution / 1000000, 2) }}M
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-2 mt-1">
                            <div class="bg-[#10b981] h-2 rounded-full" style="width: {{ $achievedPct }}%"></div>
                        </div>
                        <span class="text-[10px] mt-1 text-white/70">{{ number_format($achievedPct, 1) }}% of target</span>
                    </div>
                </div>

                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-[#f59e0b]">
                    <div class="kpi-title">Pending Payments</div>
                    <div class="kpi-value text-2xl">
                        <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                        {{ number_format($pendingPayments / 1000000, 2) }}M
                    </div>
                    <span class="text-[10px] mt-1 text-[#f59e0b]">Unpaid/Overdue Invoices</span>
                </div>

                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-[#7c3aed]">
                    <div class="kpi-title">Ongoing Deals</div>
                    <div class="kpi-value text-2xl">
                        <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                        {{ number_format($ongoingDealsValue / 1000000, 2) }}M
                    </div>
                    <span class="text-[10px] mt-1 text-[#a78bfa]">{{ $ongoingDealsCount }} Open Deals</span>
                </div>
            </div>
            @elseif(auth()->user()->role === 'HOD')
            </div>
            <div class="flex gap-4 min-w-[320px] w-full mt-4 flex-wrap">
                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-slate-500">
                    <div class="kpi-title">Dept. Target ({{ auth()->user()->department }})</div>
                    <div class="kpi-value text-2xl">
                        <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                        {{ number_format($deptTarget / 1000000, 2) }}M
                    </div>
                </div>

                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-[#10b981]">
                    <div class="kpi-title">Dept. Achieved</div>
                    @php $deptAchievedPct = $deptTarget > 0 ? min(100, ($deptActual / $deptTarget) * 100) : 0; @endphp
                    <div class="flex flex-col items-center w-full">
                        <div class="kpi-value text-2xl mb-1">
                            <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                            {{ number_format($deptActual / 1000000, 2) }}M
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-2 mt-1">
                            <div class="bg-[#10b981] h-2 rounded-full" style="width: {{ $deptAchievedPct }}%"></div>
                        </div>
                        <span class="text-[10px] mt-1 text-white/70">{{ number_format($deptAchievedPct, 1) }}% of dept target</span>
                    </div>
                </div>

                <div class="kpi-card flex-1 min-w-[200px] border-l-4 border-[#2563eb]">
                    <div class="kpi-title">Total Contribution</div>
                    <div class="kpi-value text-2xl">
                        <span class="text-primary-light/50 text-[14px] font-medium">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> 
                        {{ number_format($totalContribution / 1000000, 2) }}M
                    </div>
                </div>
            </div>
            @else
            <div class="flex flex-row gap-6 w-full xl:w-auto shrink-0 self-stretch">
                <div class="flex flex-col justify-center items-center px-10 rounded-[12px] shadow-[0_4px_12px_rgba(0,0,0,0.1)] min-w-[250px] h-full" style="background-color: #2b3547;">
                    <div class="text-[10px] font-bold text-[#f8fafc] mb-1.5 uppercase tracking-[0.1em]">Total Contribution</div>
                    <div class="text-[28px] font-bold text-white flex items-baseline gap-1.5 leading-none mt-1">
                        <span class="text-white/70 text-[14px] font-semibold">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> {{ number_format($totalContribution / 1000000, 2) }}M
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'Manager')
        <!-- Manager Dashboard Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-funnel-dollar text-orange-500"></i> Deals Progress Pipeline</h3>
                    <button onclick="downloadChart('dealsProgressChart', 'Deals_Progress')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="h-[300px]">
                    <canvas id="dealsProgressChart"></canvas>
                </div>
            </div>
            
            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-tag text-violet-500"></i> Top Brands (My Deals)</h3>
                    <button onclick="downloadChart('brandChart', 'Top_Brands')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="h-[300px]">
                    <canvas id="brandChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <div class="glass-card flex flex-col">
                <h3 class="chart-title"><i class="fas fa-trophy text-yellow-500"></i> Key Ongoing Deals</h3>
                <div class="flex-1 overflow-y-auto">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Contribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($keyCampaigns->take(10) as $campaign)
                            <tr>
                                <td class="truncate max-w-[200px]" title="{{ $campaign['description'] }}">
                                    {{ $campaign['description'] ?: 'Untitled' }}
                                </td>
                                <td class="text-right font-semibold text-[#10b981]">
                                    {{ $campaign['currency'] ?? ($deals->pluck('currency')->unique()->count() === 1 ? $deals->first()->currency : 'LKR') }} {{ number_format($campaign['contribution'] / 1000, 0) }}k
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-chart-pie text-blue-500"></i> Revenue Categories</h3>
                    <button onclick="downloadChart('revenueChart', 'Revenue_Categories')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="flex flex-col h-full">
                    <div class="h-[200px] mb-4">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div id="revenueLegend" class="grid grid-cols-2 gap-2 overflow-y-auto max-h-[100px]">
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Top Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-users"></i> Account Manager Contribution</h3>
                    <button onclick="downloadChart('managerChart', 'Manager_Contribution')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="h-[300px]">
                    <canvas id="managerChart"></canvas>
                </div>
            </div>

            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-tag"></i> Top Brands Contribution</h3>
                    <button onclick="downloadChart('brandChart', 'Brand_Contribution')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="h-[300px]">
                    <canvas id="brandChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-building"></i> Department Split</h3>
                    <button onclick="downloadChart('deptChart', 'Department_Split')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="h-[250px]">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>

            <div class="glass-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-chart-pie"></i> Revenue Categories</h3>
                    <button onclick="downloadChart('revenueChart', 'Revenue_Categories')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="flex flex-col h-full">
                    <div class="h-[200px] mb-4">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div id="revenueLegend" class="grid grid-cols-2 gap-2 overflow-y-auto max-h-[100px]">
                        <!-- Legend dynamically populated -->
                    </div>
                </div>
            </div>

            <div class="glass-card flex flex-col">
                <h3 class="chart-title"><i class="fas fa-trophy"></i> Key Campaigns</h3>
                <div class="flex-1 overflow-y-auto">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Contribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($keyCampaigns->take(10) as $campaign)
                            <tr>
                                <td class="truncate max-w-[150px]" title="{{ $campaign['description'] }}">
                                    {{ $campaign['description'] ?: 'Untitled' }}
                                </td>
                                <td class="text-right font-semibold">
                                    {{ number_format($campaign['contribution'] / 1000, 0) }}k
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Target Type Tab Content -->
    <div id="target-type-tab" class="tab-content hidden transition-all duration-300">
        <div class="flex flex-col xl:flex-row gap-6 mb-6 items-stretch">
            <div class="flex flex-row gap-6 w-full xl:flex-1 shrink-0 self-stretch">
                <div class="flex flex-col justify-center items-center px-10 rounded-[12px] shadow-[0_4px_12px_rgba(0,0,0,0.1)] min-w-[250px] h-full" style="background-color: #2b3547;">
                    <div class="text-[10px] font-bold text-[#f8fafc] mb-1.5 uppercase tracking-[0.1em]">SBU Actual</div>
                    <div class="text-[28px] font-bold text-white flex items-baseline gap-1.5 leading-none mt-1">
                        <span class="text-white/70 text-[14px] font-semibold">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> {{ number_format($sbuActual / 1000000, 2) }}M
                    </div>
                </div>
                <div class="flex flex-col justify-center items-center px-10 rounded-[12px] shadow-[0_4px_12px_rgba(0,0,0,0.1)] min-w-[250px] h-full" style="background-color: #2b3547;">
                    <div class="text-[10px] font-bold text-[#f8fafc] mb-1.5 uppercase tracking-[0.1em]">Sales Actual</div>
                    <div class="text-[28px] font-bold text-white flex items-baseline gap-1.5 leading-none mt-1">
                        <span class="text-white/70 text-[14px] font-semibold">{{ count($deals->pluck('currency')->unique()) === 1 ? $deals->first()->currency : 'LKR' }}</span> {{ number_format($salesActual / 1000000, 2) }}M
                    </div>
                </div>
            </div>
            
            <div class="ml-6 flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">View Mode</label>
                <select id="targetViewMode" onchange="updateTargetChart(this.value)" 
                    class="text-xs font-bold border-none focus:ring-0 cursor-pointer bg-slate-50 rounded-lg px-3 py-1.5 min-w-[140px]">
                    <option value="sbu">SBU Only</option>
                    <option value="sales">Sales Only</option>
                </select>
            </div>
        </div>

        <div class="glass-card">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <h3 class="chart-title mb-0"><i class="fas fa-bullseye"></i> <span id="targetChartTitle">SBU vs Sales: Actual vs Target</span></h3>
                    <button onclick="downloadChart('targetChart', 'Target_Comparison')" class="text-slate-400 hover:text-primary transition-colors text-xs flex items-center gap-2">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#2563eb]"></span> Actual</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full border-2 border-[#db2777]"></span> Target</div>
                </div>
            </div>
            <div class="h-[400px]">
                <canvas id="targetChart"></canvas>
            </div>
        </div>
    </div>

    </div>


</div>

@push('scripts')
<script>
    function switchTab(tabId) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(tabId + '-tab').classList.remove('hidden');

        // Trigger resize to fix any chart rendering issues sometimes caused by hidden containers
        window.dispatchEvent(new Event('resize'));
    }

    // Premium Color Palette
    const colors = {
        primary: '#2563eb',
        secondary: '#64748b',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        violet: '#7c3aed',
        pink: '#db2777',
        orange: '#ea580c',
        chart: [
            '#2563eb', '#7c3aed', '#db2777', '#ea580c', '#16a34a', 
            '#0891b2', '#4f46e5', '#9333ea', '#c026d3', '#e11d48'
        ]
    };

    // Common Chart Configuration
    Chart.register(ChartDataLabels);
    Chart.defaults.font.family = "'Inter', 'Segoe UI', system-ui, sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.datalabels.display = true;
    
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1500,
            easing: 'easeOutQuart'
        },
        plugins: {
            legend: { display: false }
        }
    };

    // 1. Manager Chart (Horizontal)
    const managerData = @json($managerContribution);
    new Chart(document.getElementById('managerChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(managerData),
            datasets: [{
                data: Object.values(managerData),
                backgroundColor: colors.primary,
                borderRadius: 6,
                barThickness: 18,
            }]
        },
        options: {
            ...baseOptions,
            indexAxis: 'y',
            plugins: {
                ...baseOptions.plugins,
                datalabels: {
                    color: '#fff',
                    anchor: 'end',
                    align: 'start',
                    offset: 8,
                    font: { size: 10, weight: 'bold' },
                    formatter: (v) => (v / 1000000).toFixed(1) + 'M'
                }
            },
            scales: {
                x: {
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: (v) => (v / 1000000) + 'M', font: { size: 10 } }
                },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // 2. Brand Chart (Horizontal)
    const brandData = @json($brandContribution);
    new Chart(document.getElementById('brandChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(brandData).slice(0, 10),
            datasets: [{
                data: Object.values(brandData).slice(0, 10),
                backgroundColor: colors.violet,
                borderRadius: 4,
                barThickness: 12,
            }]
        },
        options: {
            ...baseOptions,
            indexAxis: 'y',
            plugins: {
                ...baseOptions.plugins,
                datalabels: {
                    color: (ctx) => ctx.dataset.data[ctx.dataIndex] < 2000000 ? colors.secondary : '#fff',
                    anchor: 'end',
                    align: (ctx) => ctx.dataset.data[ctx.dataIndex] < 2000000 ? 'end' : 'start',
                    font: { size: 9, weight: '600' },
                    formatter: (v) => (v / 1000000).toFixed(1) + 'M'
                }
            },
            scales: {
                x: {
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: (v) => (v / 1000000) + 'M', font: { size: 10 } }
                },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // 3. Department Chart (Vertical)
    const deptData = @json($departmentContribution);
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(deptData),
            datasets: [{
                data: Object.values(deptData),
                backgroundColor: colors.chart[5],
                borderRadius: 8,
                barThickness: 32,
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                datalabels: {
                    color: colors.secondary,
                    anchor: 'end',
                    align: 'top',
                    font: { size: 10, weight: 'bold' },
                    formatter: (v) => (v / 1000000).toFixed(1) + 'M'
                }
            },
            scales: {
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: (v) => (v / 1000000) + 'M', font: { size: 10 } }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // 4. Revenue Category (Donut)
    const revDataRaw = @json($revenueCategoryContribution);
    const revLabels = Object.keys(revDataRaw);
    const revValues = Object.values(revDataRaw);
    const revTotal = revValues.reduce((a, b) => a + b, 0);

    new Chart(document.getElementById('revenueChart'), {
        type: 'doughnut',
        data: {
            labels: revLabels,
            datasets: [{
                data: revValues,
                backgroundColor: colors.chart,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 15
            }]
        },
        options: {
            ...baseOptions,
            cutout: '72%',
            plugins: {
                ...baseOptions.plugins,
                datalabels: {
                    display: (ctx) => (ctx.dataset.data[ctx.dataIndex] / (revTotal || 1)) > 0.1,
                    color: '#fff',
                    font: { size: 10, weight: 'bold' },
                    formatter: (v) => ((v / (revTotal || 1)) * 100).toFixed(0) + '%'
                }
            }
        }
    });

    // Custom Legend for Revenue
    const legendContainer = document.getElementById('revenueLegend');
    if (legendContainer) {
        revLabels.forEach((label, i) => {
            const pct = ((revValues[i] / (revTotal || 1)) * 100).toFixed(1);
            const itemHtml = `
                <div class="flex items-center gap-2 p-1 hover:bg-slate-50 rounded transition-colors cursor-default">
                    <div class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: ${colors.chart[i % colors.chart.length]}"></div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[10px] font-semibold text-slate-700 truncate">${label}</span>
                        <span class="text-[9px] text-slate-400">${pct}%</span>
                    </div>
                </div>
            `;
            legendContainer.insertAdjacentHTML('beforeend', itemHtml);
        });
    }

    // Deals Progress Chart (Manager Only)
    @if(auth()->user()->role === 'Manager')
    const progressData = @json($dealsProgress ?? []);
    if(document.getElementById('dealsProgressChart')) {
        new Chart(document.getElementById('dealsProgressChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(progressData),
                datasets: [{
                    data: Object.values(progressData),
                    backgroundColor: colors.warning,
                    borderRadius: 6,
                    barThickness: 32,
                }]
            },
            options: {
                ...baseOptions,
                plugins: {
                    ...baseOptions.plugins,
                    datalabels: {
                        color: colors.secondary,
                        anchor: 'end',
                        align: 'top',
                        font: { size: 10, weight: 'bold' },
                        formatter: (v) => v
                    }
                },
                scales: {
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, font: { size: 10 } }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }
    @endif

    // 5. Target Chart (Actual vs Target Logic)
    let targetChart = null;
    const sbuTargetDepts = {!! json_encode(\App\Models\Target::where('type', 'department')->whereIn('department', ['Creative', 'Digital', 'Tech', 'PM', 'Corporate'])->pluck('target_amount', 'department')) !!};
    const salesTargetDepts = {!! json_encode(\App\Models\Target::where('type', 'department')->whereIn('department', ['AM', 'BD'])->pluck('target_amount', 'department')) !!};

    const targetData = {
        sbuActual: {{ $sbuActual }},
        salesActual: {{ $salesActual }},
        sbuTarget: {{ $sbuTarget }},
        salesTarget: {{ $salesTarget }},
        sbuBreakdown: @json($sbuDeptActuals),
        salesBreakdown: @json($salesDeptActuals),
        sbuTargetsBreakdown: sbuTargetDepts,
        salesTargetsBreakdown: salesTargetDepts,
    };

    function updateTargetChart(mode) {
        const ctx = document.getElementById('targetChart');
        if (!ctx) return;

        let labels = [];
        let actuals = [];
        let targets = [];
        let title = "";

        if (mode === 'both') {
            labels = ['SBU', 'Sales'];
            actuals = [targetData.sbuActual, targetData.salesActual];
            targets = [targetData.sbuTarget, targetData.salesTarget];
            title = "SBU vs Sales: Actual vs Target";
        } else if (mode === 'sbu') {
            labels = Object.keys(targetData.sbuBreakdown);
            actuals = Object.values(targetData.sbuBreakdown);
            targets = labels.map(label => targetData.sbuTargetsBreakdown[label] || 0);
            title = "SBU Department Breakdown";
        } else {
            labels = Object.keys(targetData.salesBreakdown);
            actuals = Object.values(targetData.salesBreakdown);
            targets = labels.map(label => targetData.salesTargetsBreakdown[label] || 0);
            title = "Sales Department Breakdown";
        }

        document.getElementById('targetChartTitle').innerText = title;

        const chartData = {
            labels: labels,
            datasets: [
                {
                    label: 'Actual',
                    data: actuals,
                    backgroundColor: colors.primary,
                    borderRadius: 6,
                }
            ]
        };

        // Always add the target dataset to allow viewing vs target metrics within specific groups
        chartData.datasets.push({
            label: 'Target',
            data: targets,
            backgroundColor: colors.pink + '44',
            borderColor: colors.pink,
            borderWidth: 2,
            borderRadius: 6,
        });

        if (targetChart) {
            targetChart.data = chartData;
            targetChart.update();
        } else {
            targetChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    ...baseOptions,
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { display: mode === 'both', position: 'top' },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
                            formatter: (v) => v > 0 ? '{{ $deals->pluck('currency')->unique()->count() === 1 ? $deals->first()->currency : 'LKR' }} ' + (v / 1000000).toFixed(1) + 'M' : '',
                            font: { weight: 'bold', size: 10 }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: { 
                                callback: (v) => (v / 1000000) + 'M',
                                font: { size: 10 }
                            }
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    }

    // Initialize Target Chart
    updateTargetChart('sbu');

    // 6. Chart Export Functionality
    window.downloadChart = function(canvasId, filename) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('Canvas not found:', canvasId);
            return;
        }
        
        // Create a temporary link to trigger download
        const link = document.createElement('a');
        link.download = filename + '_' + new Date().toISOString().slice(0, 10) + '.png';
        
        // Use a temporary canvas to add a white background (since our dashboard uses transparency)
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const ctx = tempCanvas.getContext('2d');
        
        // Fill white background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        
        // Draw the original chart on top
        ctx.drawImage(canvas, 0, 0);
        
        link.href = tempCanvas.toDataURL('image/png');
        link.click();
    }
</script>
@endpush
@endsection
