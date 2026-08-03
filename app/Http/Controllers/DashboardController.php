<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Target;

use App\Models\Deal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Handle Filters
        $month = $request->input('month', 'all');
        $brandFilter = $request->input('brand', 'all');
        $managerFilter = $request->input('manager', 'all');
        $departmentFilter = $request->input('department', 'all');
        $stageFilter = $request->input('stage', 'all');

        $months = [
            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];
        
        // Populate brands from Estimate brand_name field
        $brands = Estimate::whereNotNull('brand_name')
            ->where('brand_name', '!=', '')
            ->distinct()
            ->orderBy('brand_name')
            ->pluck('brand_name', 'brand_name')
            ->toArray();

        $user = auth()->user();
        $userRole = $user->role;
        $userDept = $user->department;

        if ($userRole === 'Staff') {
            $pettyCashes = \App\Models\PettyCashRequest::with(['hod', 'items.category', 'proofs'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $expenseCategories = \App\Models\ExpenseCategory::where('status', 'active')->orderBy('name')->get();
            
            $hods = \App\Models\User::where('role', 'HOD');
            if ($userDept) {
                $hods->where('department', $userDept);
            }
            $hods = $hods->get();
            if ($hods->isEmpty()) {
                $hods = \App\Models\User::where('role', 'HOD')->get();
            }

            $jobQuery = \App\Models\Deal::whereNotNull('job_number');
            if ($userDept) {
                $jobQuery->where(function ($q) use ($userDept) {
                    $q->whereJsonContains('department_split', [['department' => $userDept]])
                      ->orWhereHas('owner', function ($oq) use ($userDept) {
                          $oq->where('department', $userDept);
                      });
                });
            }
            $jobs = $jobQuery->orderBy('job_number', 'desc')->pluck('job_number', 'job_number');

            return view('dashboard.staff', [
                'user' => $user,
                'hodName' => $user->hod_name,
                'pettyCashes' => $pettyCashes,
                'expenseCategories' => $expenseCategories,
                'hods' => $hods,
                'jobs' => $jobs,
            ]);
        }

        $managers = \App\Models\User::where('role', 'Manager');
        if ($userRole === 'Manager') {
            $managers->where('id', $user->id);
        } elseif ($userRole === 'HOD' && $userDept) {
            $managers->where('department', $userDept);
        }
        $managers = $managers->pluck('name', 'id');

        $departments = ['Creative', 'Digital', 'Tech', 'PM', 'Corporate'];
        if (in_array($userRole, ['HOD', 'Manager']) && $userDept) {
            $departments = [$userDept];
        }
        
        $stages = ['Objection handling', 'Finalizing terms', 'Closed Won'];
        $stages = array_combine($stages, $stages);

        // Define Category Mappings
        $sbuDepts = ['Creative', 'Digital', 'Tech', 'PM', 'Corporate'];
        $salesDepts = ['AM', 'BD'];

        // 2. Base Query context
        $query = Deal::with(['owner', 'customer', 'estimates' => function($q) {
            $q->with(['items', 'thirdPartyCosts'])->whereIn('status', ['Approved', 'Accepted', 'Ready to Invoice', 'Invoiced']);
        }]);

        // Role-based filtering
        if (!in_array($userRole, ['Super Admin', 'Management'])) {
            $query->where(function ($q) use ($user, $userDept) {
                // Own deals
                $q->where('user_id', $user->id)
                  // Team member deals
                  ->orWhereHas('teamMembers', function ($tm) use ($user) {
                      $tm->where('users.id', $user->id);
                  });
                
                // Department split check (all users in department)
                if ($userDept) {
                    $q->orWhereJsonContains('department_split', [['department' => $userDept]]);
                    $q->orWhereHas('estimates.items', function($iq) use ($userDept) {
                        $iq->where('department', $userDept);
                    });
                }
                
                // HOD specific: subordinates
                if ($user->role === 'HOD') {
                    // Deals owned by subordinates
                    $subordinateIds = \App\Models\User::where('supervisor_id', $user->id)->pluck('id');
                    if ($subordinateIds->isNotEmpty()) {
                        $q->orWhereIn('user_id', $subordinateIds);
                    }
                }
            });
        }

        if ($month !== 'all') {
            $query->whereMonth('close_date', $month);
        }
        if ($managerFilter !== 'all') {
            $query->where('user_id', $managerFilter);
        }
        
        if ($departmentFilter !== 'all') {
            if ($departmentFilter === 'SBU') {
                $query->where(function($q) use ($sbuDepts) {
                    foreach ($sbuDepts as $dept) {
                        $q->orWhereJsonContains('department_split', [['department' => $dept]]);
                    }
                });
            } elseif ($departmentFilter === 'Sales') {
                $query->where(function($q) use ($salesDepts) {
                    foreach ($salesDepts as $dept) {
                        $q->orWhereJsonContains('department_split', [['department' => $dept]]);
                    }
                });
            } else {
                $query->whereJsonContains('department_split', ['department' => $departmentFilter]);
            }
        }
        if ($stageFilter !== 'all') {
            $query->where('stage', $stageFilter);
        }
        if ($brandFilter !== 'all') {
            $query->whereHas('estimates', function($q) use ($brandFilter) {
                $q->where('brand_name', $brandFilter);
            });
        }
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        // --- NEW: Customer Filter ---
        $customerFilter = $request->input('customer', 'all');
        
        if ($customerFilter !== 'all') {
            $query->where('customer_id', $customerFilter);
        }

        $deals = $query->get();

        // Populate manager customers for the filter dropdown
        $managerCustomers = collect();
        if ($userRole === 'Manager') {
            $managerCustomers = Customer::whereHas('deals', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->pluck('name', 'id')->toArray();
        }

        // Adjust revenue/contribution metrics to exclude VAT and SSCL, and deduct third party costs
        $deals->each(function($deal) {
            $estimate = $deal->estimates->first();
            if ($estimate) {
                // Calculate total excluding VAT and SSCL: sum of amount from items
                $preTaxTotal = $estimate->items->sum(function($item) {
                    return (float)$item->amount;
                });
                
                // Calculate third party costs
                $thirdPartyTotal = $estimate->thirdPartyCosts->sum('cost');
                
                if ($preTaxTotal > 0) {
                    $deal->revenue = $preTaxTotal;
                    $deal->contribution = $preTaxTotal - $thirdPartyTotal;
                }
            }
        });

        // Determine active department/category filter depts
        $targetDepts = null;
        if (!in_array($userRole, ['Super Admin', 'Management']) && $userDept) {
            $targetDepts = [$userDept];
        } elseif ($departmentFilter === 'SBU') {
            $targetDepts = $sbuDepts;
        } elseif ($departmentFilter === 'Sales') {
            $targetDepts = $salesDepts;
        } elseif ($departmentFilter !== 'all') {
            $targetDepts = [$departmentFilter];
        }

        // 3. Calculate KPIs and Chart Data
        $totalContribution = 0;
        foreach ($deals as $deal) {
            $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
            $deptCon = 0;
            $hasMatch = false;

            if ($targetDepts && is_array($splits) && !empty($splits)) {
                foreach ($splits as $split) {
                    $splitDept = trim(strtolower($split['department'] ?? ''));
                    foreach ($targetDepts as $td) {
                        if (trim(strtolower($td)) === $splitDept) {
                            $hasMatch = true;
                            $deptCon += (float)($split['contribution_amount'] ?? 0);
                        }
                    }
                }
            }

            // Apply visibility: Owners always see 100%. 
            // Others see the share if a filter is active or if they are restricted.
            if ($deal->user_id === $user->id) {
                $totalContribution += $deal->contribution;
            } else {
                if ($targetDepts) {
                    if ($hasMatch) {
                        $totalContribution += $deptCon;
                    } else {
                        // If a filter is active and this deal doesn't have a split for it, it counts as 0
                        $totalContribution += 0;
                    }
                } else {
                    // Admins with no filter see 100%
                    $totalContribution += $deal->contribution;
                }
            }
        }

        // Horizontal Bar: Contribution - Account Manager
        $chartQuery = $deals;
        if ($userRole === 'HOD' && $userDept) {
            $chartQuery = $deals->filter(function($d) use ($userDept) {
                return ($d->owner->department ?? '') === $userDept;
            });
        }

        $managerContribution = $chartQuery->groupBy(function($d) {
            return $d->owner->name ?? 'Unknown';
        })->map(function($group) use ($targetDepts, $user) {
            $sum = 0;
            foreach ($group as $deal) {
                if ($targetDepts) {
                    $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
                    if (is_array($splits) && !empty($splits)) {
                        $hasMatch = false;
                        $deptCon = 0;
                        foreach ($splits as $split) {
                            $splitDept = trim(strtolower($split['department'] ?? ''));
                            foreach ($targetDepts as $td) {
                                if (trim(strtolower($td)) === $splitDept) {
                                    $hasMatch = true;
                                    $deptCon += (float)($split['contribution_amount'] ?? 0);
                                }
                            }
                        }
                        
                        if ($deal->user_id === $user->id) {
                            $sum += $deal->contribution;
                        } elseif ($hasMatch) {
                            $sum += $deptCon;
                        }
                        if ($deal->user_id === $user->id) {
                            $sum += $deal->contribution;
                        } elseif ($hasMatch) {
                            $sum += $deptCon;
                        }
                    } elseif ($deal->user_id === $user->id) {
                        $sum += $deal->contribution;
                    }
                } else {
                    $sum += $deal->contribution;
                }
            }
            return $sum;
        })
->filter(fn($sum) => $sum > 0)->sortDesc();

        // Vertical Bar: Departmentwise Contribution
        $departmentContribution = [];
        foreach ($departments as $dept) {
            $departmentContribution[$dept] = 0;
        }

        foreach ($deals as $deal) {
            if ($deal->department_split) {
                $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
                if (is_array($splits)) {
                    foreach ($splits as $split) {
                        $deptName = $split['department'] ?? null;
                        if ($deptName && isset($departmentContribution[$deptName])) {
                            // If targetDepts is set, only show depts in that list
                            if ($targetDepts && !in_array($deptName, $targetDepts)) {
                                continue;
                            }
                            $departmentContribution[$deptName] += (float)($split['contribution_amount'] ?? 0);
                        }
                    }
                }
            }
        }
        arsort($departmentContribution);

        // Horizontal Bar: Brandwise Contribution
        $brandContribution = [];
        foreach ($deals as $deal) {
            foreach ($deal->estimates as $est) {
                $brand = $est->brand_name ?? 'Unknown';
                if ($brand === 'Unknown' || empty($brand)) {
                    if ($deal->customer && !empty($deal->customer->brand)) {
                        $brand = $deal->customer->brand;
                    }
                }

                $contribution = 0;
                if ($targetDepts && $deal->user_id !== $user->id) {
                    // Precision item-level aggregation for HODs and filtered departments
                    if ($est->items->isNotEmpty()) {
                        foreach ($est->items as $item) {
                            if (in_array($item->department ?? '', $targetDepts)) {
                                $contribution += (float)$item->amount;
                            }
                        }
                        // Subtract third party costs for target departments
                        foreach ($est->thirdPartyCosts as $tpc) {
                            if (in_array($tpc->department ?? '', $targetDepts)) {
                                $contribution -= (float)$tpc->cost;
                            }
                        }
                    }
                } else {
                    // Owners or Admins see the full contribution of items
                    $contribution = $est->items->sum(function($item) {
                        return (float)$item->amount;
                    }) - $est->thirdPartyCosts->sum('cost');
                }

                if ($brand !== 'Unknown' && $contribution > 0) {
                    $brandContribution[$brand] = ($brandContribution[$brand] ?? 0) + $contribution;
                }
            }

            // Fallback: If no estimates worked for this department but the split exists, handle it
            // (For HODs/Owners where department_split might list them even if items aren't tagged)
            if (($userRole === 'HOD' || $deal->user_id === $user->id) && $userDept && $deal->estimates->isEmpty()) {
                $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
                if (is_array($splits)) {
                    foreach ($splits as $split) {
                        if (($split['department'] ?? '') === $userDept && (float)($split['contribution_amount'] ?? 0) > 0) {
                            $brand = $deal->customer->brand ?? 'Unknown';
                            $brandContribution[$brand] = ($brandContribution[$brand] ?? 0) + (float)$split['contribution_amount'];
                        }
                    }
                }
            }
        }
        $brandContribution = array_filter($brandContribution, fn($v) => $v > 0);
        arsort($brandContribution);

        // Donut: Revenuewise contribution (Summing item amounts grouped by category)
        $revenueCategoryContribution = [];
        foreach ($deals as $deal) {
            $approvedEstimate = $deal->estimates->first();
            if ($approvedEstimate && $approvedEstimate->items) {
                foreach ($approvedEstimate->items as $item) {
                    $category = $item->revenue_category ?? 'Uncategorized';
                    $amount = $item->amount;
                    $itemDept = $item->department ?? '';

                    // Filter by targetDepts if active, unless user is the owner
                    if ($targetDepts && !in_array($itemDept, $targetDepts) && $deal->user_id !== $user->id) {
                        continue;
                    }

                    $revenueCategoryContribution[$category] = ($revenueCategoryContribution[$category] ?? 0) + $amount;
                }
            }
        }
        arsort($revenueCategoryContribution);

        // Key Campaigns Table
        $keyCampaigns = $deals->map(function($d) use ($targetDepts, $user) {
            $approvedEstimate = $d->estimates->first();
            $title = $d->title;
            if ($approvedEstimate && $approvedEstimate->description) {
                $title = $approvedEstimate->description;
            }

            $contribution = 0;
            if ($targetDepts) {
                $splits = is_string($d->department_split) ? json_decode($d->department_split, true) : $d->department_split;
                if (is_array($splits) && !empty($splits)) {
                    foreach ($splits as $split) {
                        if (in_array($split['department'] ?? '', $targetDepts)) {
                            $contribution += (float)($split['contribution_amount'] ?? 0);
                        }
                    }
                    
                    if ($d->user_id === $user->id) {
                        // Owners always get 100% credit for their campaigns
                        $contribution = $d->contribution;
                    }
                } elseif ($d->user_id === $user->id) {
                    $contribution = $d->contribution;
                }
            } else {
                $contribution = $d->contribution;
            }

            return [
                'description' => $title,
                'contribution' => $contribution
            ];
        })->filter(fn($item) => $item['contribution'] > 0)->sortByDesc('contribution')->values();
        
        // Target Type Logic
        $sbuDepts = ['Creative', 'Digital', 'Tech', 'PM', 'Corporate'];
        $salesDepts = ['AM', 'BD'];
        
        $sbuActual = 0;
        $salesActual = 0;
        $sbuDeptActuals = array_fill_keys($sbuDepts, 0);
        $salesDeptActuals = array_fill_keys($salesDepts, 0);

        foreach ($deals as $deal) {
            $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
            if (is_array($splits) && !empty($splits)) {
                foreach ($splits as $split) {
                    $deptName = $split['department'] ?? '';
                    $amount = (float)($split['contribution_amount'] ?? 0);
                    
                    if (in_array($deptName, $sbuDepts)) {
                        $sbuActual += $amount;
                        $sbuDeptActuals[$deptName] += $amount;
                    } elseif (in_array($deptName, $salesDepts)) {
                        $salesActual += $amount;
                        $salesDeptActuals[$deptName] += $amount;
                    }
                }
            } else {
                // If no split defined, 100% goes to the owner's department
                $ownerDept = $deal->owner->department ?? '';
                $amount = (float)($deal->contribution ?? 0);
                
                if (in_array($ownerDept, $sbuDepts)) {
                    $sbuActual += $amount;
                    $sbuDeptActuals[$ownerDept] += $amount;
                } elseif (in_array($ownerDept, $salesDepts)) {
                    $salesActual += $amount;
                    $salesDeptActuals[$ownerDept] += $amount;
                }
            }
        }

        $sbuTarget = Target::where('type', 'department')->whereIn('department', $sbuDepts)->sum('target_amount');
        $salesTarget = Target::where('type', 'department')->whereIn('department', $salesDepts)->sum('target_amount');

        $userTarget = Target::where('type', 'user')->where('user_id', $user->id)->value('target_amount') ?? 0;

        // --- NEW: Manager & HOD Specific Metrics ---
        $pendingPayments = 0;
        $ongoingDealsCount = 0;
        $ongoingDealsValue = 0;
        $dealsProgress = [];
        $deptTarget = 0;
        $deptActual = 0;

        if ($userRole === 'Manager' || $userRole === 'HOD') {
            if ($userDept) {
                $deptTarget = Target::where('type', 'department')
                    ->where('department', $userDept)
                    ->sum('target_amount');
                
                // Calculate department actuals for the filtered month
                foreach ($deals as $deal) {
                    $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
                    if (is_array($splits)) {
                        foreach ($splits as $split) {
                            if (($split['department'] ?? '') === $userDept) {
                                $deptActual += (float)($split['contribution_amount'] ?? 0);
                            }
                        }
                    }
                }
            }

            if ($userRole === 'Manager') {
                // Pending Payments: Unpaid/overdue non-proforma invoices tied to Manager's deals
                $pendingPayments = Invoice::where('is_proforma', false)
                    ->whereIn('invoices.status', ['unpaid', 'overdue'])
                    ->whereHas('estimate.deal', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                    ->sum('invoice_items.amount');

                // Ongoing Deals (not Closed Won or Rejected)
                $ongoingDeals = $deals->whereNotIn('stage', ['Closed Won', 'Rejected']);
                $ongoingDealsCount = $ongoingDeals->count();
                $ongoingDealsValue = $ongoingDeals->sum('contribution');
                
                // Deals Progress Breakdown
                $dealsProgress = $deals->groupBy('stage')->map->count()->toArray();
            }
        }

        return view('dashboard', compact(
            'month', 'brandFilter', 'managerFilter', 'departmentFilter', 'stageFilter', 'customerFilter',
            'months', 'brands', 'managers', 'departments', 'stages', 'managerCustomers',
            'totalContribution', 'managerContribution', 'departmentContribution',
            'brandContribution', 'revenueCategoryContribution', 'keyCampaigns',
            'sbuActual', 'salesActual', 'sbuTarget', 'salesTarget',
            'sbuDeptActuals', 'salesDeptActuals', 'userTarget',
            'pendingPayments', 'ongoingDealsCount', 'ongoingDealsValue', 'dealsProgress',
            'deptTarget', 'deptActual', 'deals'
        ));
    }

    public function exportCsv(Request $request)
    {
        // Re-use filtering logic from index()
        $month = $request->input('month', 'all');
        $brandFilter = $request->input('brand', 'all');
        $managerFilter = $request->input('manager', 'all');
        $departmentFilter = $request->input('department', 'all');
        $stageFilter = $request->input('stage', 'all');
        $customerFilter = $request->input('customer', 'all');

        $user = auth()->user();
        $userRole = $user->role;
        $userDept = $user->department;

        // Category Mappings
        $sbuDepts = ['Creative', 'Digital', 'Tech', 'PM', 'Corporate'];
        $salesDepts = ['AM', 'BD'];

        $query = Deal::with(['owner', 'customer', 'estimates' => function($q) {
            $q->with(['items', 'thirdPartyCosts'])->whereIn('status', ['Approved', 'Accepted', 'Ready to Invoice', 'Invoiced']);
        }]);
        
        // Role-based filtering
        if ($userRole === 'HOD' && $userDept) {
            $query->where(function($q) use ($userDept) {
                $q->whereJsonContains('department_split', ['department' => $userDept])
                  ->orWhereHas('estimates.items', function($iq) use ($userDept) {
                      $iq->where('department', $userDept);
                  });
            });
        } elseif ($userRole === 'Manager') {
            $query->where('user_id', $user->id);
        }

        if ($month !== 'all') {
            $query->whereMonth('close_date', $month);
        }
        if ($managerFilter !== 'all') {
            $query->where('user_id', $managerFilter);
        }
        if ($departmentFilter !== 'all') {
            if ($departmentFilter === 'SBU') {
                $query->where(function($q) use ($sbuDepts) {
                    foreach ($sbuDepts as $dept) {
                        $q->orWhereJsonContains('department_split', ['department' => $dept]);
                    }
                });
            } elseif ($departmentFilter === 'Sales') {
                $query->where(function($q) use ($salesDepts) {
                    foreach ($salesDepts as $dept) {
                        $q->orWhereJsonContains('department_split', ['department' => $dept]);
                    }
                });
            } else {
                $query->whereJsonContains('department_split', ['department' => $departmentFilter]);
            }
        }
        if ($stageFilter !== 'all') {
            $query->where('stage', $stageFilter);
        }
        if ($brandFilter !== 'all') {
            $query->whereHas('estimates', function($q) use ($brandFilter) {
                $q->where('brand_name', $brandFilter);
            });
        }
        if ($customerFilter !== 'all') {
            $query->where('customer_id', $customerFilter);
        }

        $deals = $query->get();

        // Adjust revenue/contribution metrics to exclude VAT and SSCL, and deduct third party costs
        $deals->each(function($deal) {
            $estimate = $deal->estimates->first();
            if ($estimate) {
                // Calculate total excluding VAT and SSCL: sum of amount from items
                $preTaxTotal = $estimate->items->sum(function($item) {
                    return (float)$item->amount;
                });
                
                // Calculate third party costs
                $thirdPartyTotal = $estimate->thirdPartyCosts->sum('cost');
                
                if ($preTaxTotal > 0) {
                    $deal->revenue = $preTaxTotal;
                    $deal->contribution = $preTaxTotal - $thirdPartyTotal;
                }
            }
        });

        // For HODs, only export deals owned by their department managers
        if ($userRole === 'HOD' && $userDept) {
            $deals = $deals->filter(function($d) use ($userDept) {
                return ($d->owner->department ?? '') === $userDept;
            });
        }

        $filename = "dashboard_export_" . now()->format('YmdHis') . ".csv";
        $headers = ['Date', 'Deal Title', 'Customer', 'Account Manager', 'Brand', 'Stage', 'Contribution (LKR)'];

        $callback = function () use ($deals, $headers, $userRole, $userDept, $departmentFilter, $sbuDepts, $salesDepts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($deals as $deal) {
                $itemsHandled = false;
                foreach ($deal->estimates as $est) {
                    $contribution = 0;
                    $brand = $est->brand_name ?? 'Unknown';
                    if ($brand === 'Unknown' || empty($brand)) {
                        if ($deal->customer && !empty($deal->customer->brand)) {
                            $brand = $deal->customer->brand;
                        }
                    }

                    if ($deal->user_id === auth()->id()) {
                        // Owners always see 100% of their deals
                        $contribution = $est->items->sum(function($i) {
                            return (float)$i->amount;
                        }) - $est->thirdPartyCosts->sum('cost');
                    } elseif ($userRole === 'HOD' && $userDept) {
                        if ($est->items->isNotEmpty()) {
                            foreach ($est->items as $item) {
                                if (($item->department ?? '') === $userDept) {
                                    $contribution += (float)$item->amount;
                                }
                            }
                            // Subtract third party costs for this department
                            foreach ($est->thirdPartyCosts as $tpc) {
                                if (($tpc->department ?? '') === $userDept) {
                                    $contribution -= (float)$tpc->cost;
                                }
                            }
                        }
                    } elseif ($departmentFilter === 'SBU' || $departmentFilter === 'Sales') {
                        $targetDepts = ($departmentFilter === 'SBU') ? $sbuDepts : $salesDepts;
                        if ($est->items->isNotEmpty()) {
                            foreach ($est->items as $item) {
                                if (in_array($item->department ?? '', $targetDepts)) {
                                    $contribution += (float)$item->amount;
                                }
                            }
                            // Subtract third party costs for target departments
                            foreach ($est->thirdPartyCosts as $tpc) {
                                if (in_array($tpc->department ?? '', $targetDepts)) {
                                    $contribution -= (float)$tpc->cost;
                                }
                            }
                        }
                    } elseif ($deal->user_id === auth()->id()) {
                        // Owners always see 100% of their deals
                        $contribution = $est->items->sum(function($i) {
                            return (float)$i->amount;
                        }) - $est->thirdPartyCosts->sum('cost');
                    } else {
                        // Non-owners with no active filter/matching dept see 0
                        $contribution = 0;
                    }

                    if ($contribution > 0) {
                        $itemsHandled = true;
                        fputcsv($file, [
                            $deal->close_date ? date('Y-m-d', strtotime($deal->close_date)) : 'N/A',
                            $deal->title,
                            $deal->customer->name ?? 'N/A',
                            $deal->owner->name ?? 'N/A',
                            $brand,
                            $deal->stage,
                            $contribution
                        ]);
                    }
                }

                // Fallback for HODs if no estimates worked but split exists (matching index logic)
                if (!$itemsHandled && $userRole === 'HOD' && $userDept) {
                    $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
                    if (is_array($splits)) {
                        foreach ($splits as $split) {
                            if (($split['department'] ?? '') === $userDept && (float)($split['contribution_amount'] ?? 0) > 0) {
                                fputcsv($file, [
                                    $deal->close_date ? date('Y-m-d', strtotime($deal->close_date)) : 'N/A',
                                    $deal->title,
                                    $deal->customer->name ?? 'N/A',
                                    $deal->owner->name ?? 'N/A',
                                    $deal->customer->brand ?? 'N/A',
                                    $deal->stage,
                                    (float)$split['contribution_amount']
                                ]);
                            }
                        }
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
