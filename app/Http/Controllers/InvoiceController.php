<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\TempInvoice;
use App\Traits\LogsActivity;
use App\Traits\NotifiesStakeholders;
use App\Notifications\EstimateStatusChangedNotification;

class InvoiceController extends Controller
{
    use LogsActivity, NotifiesStakeholders;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'estimate.deal', 'estimate.thirdPartyCosts' => function($q) {
            $q->whereNotNull('file_path')->where('file_path', '!=', '');
        }])->where('is_proforma', false);

        // RBAC Access Control
        $user = auth()->user();
        $managers = collect();
        if ($user->role === 'HOD' && $user->department) {
            $dept = $user->department;
            $managers = \App\Models\User::where('department', $dept)->where('role', 'Manager')->pluck('name', 'id');
            
            $query->whereHas('estimate', function ($q) use ($dept, $request) {
                $q->where(function($sq) use ($dept) {
                    $sq->whereHas('user', function($uq) use ($dept) {
                        $uq->where('department', $dept);
                    })->orWhereHas('deal', function ($dq) use ($dept) {
                        $dq->where(function ($dsq) use ($dept) {
                            $dsq->whereHas('owner', function ($oq) use ($dept) {
                                $oq->where('department', $dept);
                            })->orWhereJsonContains('department_split', ['department' => $dept])
                              ->orWhereHas('estimates.items', function ($iq) use ($dept) {
                                $iq->where('department', $dept);
                            });
                        });
                    });
                });

                if ($request->filled('manager_id')) {
                    $q->where('user_id', $request->manager_id);
                }
            });
        } elseif ($user->role === 'Manager') {
            $query->whereHas('estimate', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        } elseif (!in_array($user->role, ['Super Admin', 'Management'])) {
            $query->whereHas('estimate', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $invoices = $query->latest()->get();
        return view('invoices.index', compact('invoices', 'managers'));
    }

    public function ready(Request $request)
    {
        $query = \App\Models\TempInvoice::with(['customer', 'estimate.user', 'estimate.deal', 'estimate.thirdPartyCosts' => function($q) {
            $q->whereNotNull('file_path')->where('file_path', '!=', '');
        }]);

        // RBAC Access Control
        $user = auth()->user();
        $managers = collect();
        if ($user->role === 'HOD' && $user->department) {
            $dept = $user->department;
            $managers = \App\Models\User::where('department', $dept)->where('role', 'Manager')->pluck('name', 'id');
            
            $query->where(function($q) use ($dept) {
                $q->whereHas('estimate.user', function($uq) use ($dept) {
                    $uq->where('department', $dept);
                })->orWhereHas('estimate.deal', function ($dq) use ($dept) {
                    $dq->where(function ($dsq) use ($dept) {
                        $dsq->whereHas('owner', function ($oq) use ($dept) {
                            $oq->where('department', $dept);
                        })->orWhereJsonContains('department_split', ['department' => $dept])
                          ->orWhereHas('estimates.items', function ($iq) use ($dept) {
                            $iq->where('department', $dept);
                        });
                    });
                });
            });

            if ($request->filled('manager_id')) {
                $query->whereHas('estimate', function ($q) use ($request) {
                    $q->where('user_id', $request->manager_id);
                });
            }
        } elseif ($user->role === 'Manager') {
            $query->whereHas('estimate', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        } elseif (!in_array($user->role, ['Super Admin', 'Management'])) {
            $query->whereHas('estimate', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('temp_invoice_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $estimates = $query->latest()->get();
        return view('invoices.ready', compact('estimates', 'managers'));
    }

    public function invoiced(Request $request)
    {
        $query = Estimate::with(['customer', 'deal', 'thirdPartyCosts' => function($q) {
            $q->whereNotNull('file_path')->where('file_path', '!=', '');
        }])->whereIn('status', ['invoiced', 'approved']);

        // RBAC Access Control
        $user = auth()->user();
        $managers = collect();
        if ($user->role === 'HOD' && $user->department) {
            $dept = $user->department;
            $managers = \App\Models\User::where('department', $dept)->where('role', 'Manager')->pluck('name', 'id');
            
            $query->where(function($q) use ($dept) {
                $q->whereHas('user', function($uq) use ($dept) {
                    $uq->where('department', $dept);
                })->orWhereHas('deal', function ($dq) use ($dept) {
                    $dq->where(function ($dsq) use ($dept) {
                        $dsq->whereHas('owner', function ($oq) use ($dept) {
                            $oq->where('department', $dept);
                        })->orWhereJsonContains('department_split', ['department' => $dept])
                          ->orWhereHas('estimates.items', function ($iq) use ($dept) {
                            $iq->where('department', $dept);
                        });
                    });
                });
            });

            if ($request->filled('manager_id')) {
                $query->where('user_id', $request->manager_id);
            }
        } elseif ($user->role === 'Manager') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        } elseif (!in_array($user->role, ['Super Admin', 'Management'])) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $estimates = $query->latest()->get();
        return view('invoices.invoiced', compact('estimates', 'managers'));
    }

    public function proforma(Request $request)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return redirect()->route('invoices.index')->with('error', 'Unauthorized access.');
        }

        $query = Invoice::with(['customer', 'estimate.thirdPartyCosts' => function($q) {
            $q->whereNotNull('file_path')->where('file_path', '!=', '');
        }])->where('is_proforma', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $invoices = $query->latest()->get();
        return view('invoices.proforma', compact('invoices'));
    }

    public function rejected(Request $request)
    {
        $query = Estimate::with(['customer', 'deal', 'thirdPartyCosts' => function($q) {
            $q->whereNotNull('file_path')->where('file_path', '!=', '');
        }])->where('status', 'rejected');

        // RBAC Access Control
        $user = auth()->user();
        $managers = collect();
        if ($user->role === 'HOD' && $user->department) {
            $dept = $user->department;
            $managers = \App\Models\User::where('department', $dept)->where('role', 'Manager')->pluck('name', 'id');
            
            $query->where(function($q) use ($dept) {
                $q->whereHas('user', function($uq) use ($dept) {
                    $uq->where('department', $dept);
                })->orWhereHas('deal', function ($dq) use ($dept) {
                    $dq->where(function ($dsq) use ($dept) {
                        $dsq->whereHas('owner', function ($oq) use ($dept) {
                            $oq->where('department', $dept);
                        })->orWhereJsonContains('department_split', ['department' => $dept])
                          ->orWhereHas('estimates.items', function ($iq) use ($dept) {
                            $iq->where('department', $dept);
                        });
                    });
                });
            });

            if ($request->filled('manager_id')) {
                $query->where('user_id', $request->manager_id);
            }
        } elseif ($user->role === 'Manager') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        } elseif (!in_array($user->role, ['Super Admin', 'Management'])) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('deal', function ($dq) use ($user) {
                      $dq->where('user_id', $user->id);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $estimates = $query->latest()->get();
        return view('invoices.rejected', compact('estimates', 'managers'));
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $invoice = Invoice::with(['items'])->findOrFail($id);
        
        $user = auth()->user();
        $readonly = !in_array($user->role, ['Super Admin', 'Management']);

        $customers = Customer::all();
        $standardTerms = \App\Models\StandardTerm::all();
        $currencies = \App\Models\SystemCurrency::all();
        $ssclRate = \App\Models\Setting::get('sscl_rate', 2.5641);
        $vatRate = \App\Models\Setting::get('vat_rate', 15);
        
        $estimateBrands = Estimate::whereNotNull('brand_name')->distinct()->pluck('brand_name');
        $customerBrands = Customer::whereNotNull('brand')->distinct()->pluck('brand');
        $brands = $estimateBrands->concat($customerBrands)->unique()->sort()->values();
        
        $users = \App\Models\User::whereIn('role', ['HOD', 'Management'])->get();

        return view('invoices.edit', compact(
            'invoice',
            'customers',
            'standardTerms',
            'currencies',
            'ssclRate',
            'vatRate',
            'brands',
            'users',
            'readonly'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = Invoice::findOrFail($id);
        $user = auth()->user();
        if (!in_array($user->role, ['Super Admin', 'Management'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand_name' => 'required|string|max:255',
            'date' => 'required|date',
            'attention_to' => 'nullable|string',
            'date_of_delivery' => 'nullable|date',
            'place_of_supply' => 'nullable|string|max:255',
            'currency' => 'required|string',
            'senior_manager' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.unit_price' => 'required|numeric|between:-999999999,999999999',
            'items.*.position' => 'required|integer',
            'items.*.department' => 'required|string',
            'items.*.revenue_category' => 'required|string',
            'proforma_invoice' => 'nullable|string|in:yes,no',
            'invoice_type' => 'nullable|string|in:invoice,tax_invoice',
            'third_party_cost' => 'nullable|string|in:yes,no',
            'proforma_percentage' => 'nullable|numeric|min:0|max:100',
            'proforma_tax' => 'nullable|string|in:with_tax,without_tax',
            'advance_received_amount' => 'nullable|numeric|min:0',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'address_line_3' => 'nullable|string',
            'designation' => 'nullable|string',
            'heading' => 'nullable|string',
            'terms' => 'nullable|string',
            'special_terms' => 'nullable|string',
            'advance_payment' => 'nullable|string',
            'advance_percentage' => 'nullable|numeric',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $invoice->update([
                'customer_id' => $request->customer_id,
                'brand_name' => $request->brand_name,
                'date' => $request->date,
                'due_date' => \Carbon\Carbon::parse($request->date)->addMonth()->format('Y-m-d'),
                'attention_to' => $request->attention_to,
                'designation' => $request->designation,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'address_line_3' => $request->address_line_3,
                'currency' => $request->currency,
                'heading' => $request->heading,
                'terms' => $request->terms,
                'special_terms' => $request->special_terms,
                'advance_payment' => $request->advance_payment,
                'advance_percentage' => $request->advance_percentage,
                'advance_received_amount' => $request->advance_received_amount,
                'invoice_type' => $request->invoice_type,
                'senior_manager' => $request->senior_manager,
                'additional_information' => $request->additional_notes,
                'sscl_applicable' => $request->has('sscl_applicable') ? 1 : 0,
                'vat_applicable' => $request->has('vat_applicable') ? 1 : 0,
                'proforma_percentage' => $request->proforma_percentage,
                'proforma_tax' => $request->proforma_tax,
                'is_proforma' => ($request->proforma_invoice === 'yes') ? 1 : 0,
            ]);

            // Sync items
            $invoice->items()->delete();
            $grandTotal = 0;

            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];

                $ssclRate = \App\Models\Setting::get('sscl_rate', 2.5641) / 100;
                $vatRate = \App\Models\Setting::get('vat_rate', 15) / 100;
                
                $ssclApplicable = $request->has('sscl_applicable');
                $vatApplicable = $request->has('vat_applicable');

                $baseUnitPrice = round($unitPrice, 2);
                $amount = round($quantity * $baseUnitPrice, 2);

                $sscl = 0;
                $vat = 0;
                if ($ssclApplicable) {
                    $sscl = round($amount * $ssclRate, 2);
                }
                if ($vatApplicable) {
                    $vat = round(($amount + $sscl) * $vatRate, 2);
                }
                $totalWithVat = round($amount + $vat + $sscl, 2);
                
                $grandTotal += $totalWithVat;

                $invoice->items()->create([
                    'description' => $item['description'],
                    'type' => $item['type'] ?? 'item',
                    'quantity' => $quantity,
                    'unit_price' => $baseUnitPrice,
                    'amount' => $amount,
                    'sscl_amount' => $sscl,
                    'vat_amount' => $vat,
                    'total_with_vat' => $totalWithVat,
                    'department' => $item['department'] ?? null,
                    'revenue_category' => $item['revenue_category'] ?? null,
                ]);
            }

            // Update total amount on the invoice
            $invoice->update([
                'total_amount' => $grandTotal - ($request->advance_received_amount ?? 0)
            ]);

            $this->logAction("Updated invoice {$invoice->invoice_number}", $invoice);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to update invoice: ' . $e->getMessage());
            return back()->with('error', 'Failed to update invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403, 'Only Super Admin can change invoice status.');
        }

        $request->validate([
            'status' => 'required|in:unpaid,paid,overdue'
        ]);

        $invoice->update(['status' => $request->status]);
        $this->logAction("Updated status to {$request->status} for invoice: {$invoice->invoice_number}", $invoice);

        return back()->with('success', 'Invoice status updated successfully.');
    }

    public function duplicate(Invoice $invoice)
    {
        $invoice->load(['customer', 'items', 'estimate']);

        // Create new Estimate
        $newEstimate = Estimate::create([
            'customer_id' => $invoice->customer_id,
            'reference_number' => Estimate::generateReferenceNumber(),
            'date' => now(),
            'total_amount' => $invoice->total_amount,
            'status' => 'draft',
            'attention_to' => $invoice->attention_to ?? ($invoice->estimate->attention_to ?? null),
            'address_line_1' => $invoice->address_line_1 ?? ($invoice->estimate->address_line_1 ?? null),
            'address_line_2' => $invoice->address_line_2 ?? ($invoice->estimate->address_line_2 ?? null),
            'address_line_3' => $invoice->address_line_3 ?? ($invoice->estimate->address_line_3 ?? null),
            'designation' => $invoice->designation ?? ($invoice->estimate->designation ?? null),
            'currency' => $invoice->currency ?? ($invoice->estimate->currency ?? 'LKR'),
            'heading' => $invoice->heading ?? ($invoice->estimate->heading ?? null),
            'terms' => $invoice->terms ?? ($invoice->estimate->terms ?? null),
            'special_terms' => $invoice->special_terms ?? ($invoice->estimate->special_terms ?? null),
            'advance_payment' => $invoice->advance_payment ?? ($invoice->estimate->advance_payment ?? null),
            'advance_percentage' => $invoice->advance_percentage ?? ($invoice->estimate->advance_percentage ?? null),
            'senior_manager' => $invoice->senior_manager ?? ($invoice->estimate->senior_manager ?? null),
            'additional_notes' => $invoice->additional_information ?? ($invoice->estimate->additional_notes ?? null),
            'sscl_applicable' => $invoice->sscl_applicable ?? ($invoice->estimate->sscl_applicable ?? false),
            'vat_applicable' => $invoice->vat_applicable ?? ($invoice->estimate->vat_applicable ?? false),
        ]);

        // Copy Items
        foreach ($invoice->items as $item) {
            $newEstimate->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'sscl_amount' => $item->sscl_amount,
                'vat_amount' => $item->vat_amount,
                'total_with_vat' => $item->total_with_vat,
                'type' => $item->type ?? 'item',
            ]);
        }

        $this->logAction("Duplicated invoice: {$invoice->invoice_number} to new estimate", $invoice);

        return redirect()->route('estimates.edit', $newEstimate->id)->with('success', 'Invoice duplicated to a new Estimate.');
    }

    public function destroy(string $id)
    {
        $invoice = Invoice::findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->items()->delete();
        $invoice->delete();
        $this->logAction("Deleted invoice: {$num}");

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function revertToPending(TempInvoice $tempInvoice)
    {
        $user = auth()->user();
        $estimate = $tempInvoice->estimate;

        if (!$estimate) {
            return back()->with('error', 'Estimate not found for this temporary invoice.');
        }

        if (!$estimate->canEdit($user)) {
            return back()->with('error', 'You do not have permission to revert this estimate.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $oldStatus = $estimate->status;

            // 1. Update the estimate status back to 'draft' (Pending)
            $estimate->update(['status' => 'draft']);

            // 2. Log Action
            $this->logAction("Reverted estimate {$estimate->reference_number} back to pending/draft status", $estimate);

            // 3. Notify Stakeholders
            if ($estimate->deal) {
                $this->notifyStakeholders($estimate->deal, new EstimateStatusChangedNotification($estimate, $oldStatus, 'draft', $user));
            }

            // 4. Delete the TempInvoice and its items
            $tempInvoice->items()->delete();
            $tempInvoice->delete();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('invoices.ready')->with('success', 'Estimate reverted to pending state successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to revert estimate to pending: ' . $e->getMessage());
            return back()->with('error', 'Failed to revert estimate to pending: ' . $e->getMessage());
        }
    }
}

