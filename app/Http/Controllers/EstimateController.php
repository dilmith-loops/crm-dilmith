<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\StandardTerm;
use App\Models\SystemCurrency;
use App\Models\ThirdPartyCost;
use App\Models\TempInvoice;
use App\Notifications\EstimateCreatedNotification;
use App\Notifications\EstimateStatusChangedNotification;
use App\Notifications\EstimateInvoicedNotification;
use App\Traits\LogsActivity;
use App\Traits\NotifiesStakeholders;

class EstimateController extends Controller
{
    use LogsActivity, NotifiesStakeholders;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Estimate::with(['customer', 'deal', 'thirdPartyCosts' => function($q) {
            $q->whereNotNull('file_path')->where('file_path', '!=', '');
        }])->whereIn('status', ['draft', 'ready_to_invoice', 'approved']);

        // RBAC Access Control
        $user = auth()->user();
        $managers = collect();
        if ($user->role === 'HOD' && $user->department) {
            $dept = $user->department;
            $managers = \App\Models\User::where('department', $dept)->where('role', 'Manager')->pluck('name', 'id');
            
            $query->where(function ($q) use ($dept, $request) {
                // Deal-based visibility
                $q->whereHas('deal', function ($dq) use ($dept, $request) {
                    $dq->where(function ($sq) use ($dept) {
                        $sq->whereHas('owner', function ($oq) use ($dept) {
                            $oq->where('department', $dept);
                        })->orWhereJsonContains('department_split', ['department' => $dept])
                        ->orWhereHas('estimates.items', function ($iq) use ($dept) {
                            $iq->where('department', $dept);
                        });
                    });

                    if ($request->filled('manager_id')) {
                        $dq->where('user_id', $request->manager_id);
                    }
                })
                // Creator-based visibility (for estimates without deals)
                ->orWhere(function ($cq) use ($dept) {
                    $cq->whereNull('deal_id')->whereHas('user', function ($uq) use ($dept) {
                        $uq->where('department', $dept);
                    });
                });
            });
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
                      if ($user->department) {
                          $dq->orWhereJsonContains('department_split', [['department' => $user->department]]);
                      }
                  });
            });
        }

        // Search filter (Reference or Customer Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                    ->orWhere('brand_name', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Date Range filter
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $estimates = $query->latest()->get();
        return view('estimates.index', compact('estimates', 'managers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::all();
        $standardTerms = \App\Models\StandardTerm::all();
        $currencies = \App\Models\SystemCurrency::all();
        $ssclRate = \App\Models\Setting::get('sscl_rate', 2.5641);
        $vatRate = \App\Models\Setting::get('vat_rate', 15);
        $estimateBrands = Estimate::whereNotNull('brand_name')->distinct()->pluck('brand_name');
        $customerBrands = Customer::whereNotNull('brand')->distinct()->pluck('brand');
        $brands = $estimateBrands->concat($customerBrands)->unique()->sort()->values();
        $nextReferenceNumber = Estimate::generateReferenceNumber();
        $users = \App\Models\User::whereIn('role', ['HOD', 'Management'])->get();

        $deal = null;
        if ($request->has('deal_id')) {
            $deal = \App\Models\Deal::with(['customer'])->find($request->deal_id);
        }

        return view('estimates.create', compact('customers', 'standardTerms', 'currencies', 'ssclRate', 'vatRate', 'brands', 'nextReferenceNumber', 'users', 'deal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand_name' => 'required|string|max:255',
            'date' => 'required|date',
            'attention_to' => 'nullable|string',
            'date_of_delivery' => 'nullable|date',
            'place_of_supply' => 'nullable|string|max:255',
            'currency' => 'required|string',
            'senior_manager' => 'nullable|string',
            'deal_id' => 'nullable|exists:deals,id',
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
            'third_party_costs' => 'required_if:third_party_cost,yes|array',
            'third_party_costs.*.supplier' => 'required_if:third_party_cost,yes|string|max:255',
            'third_party_costs.*.cost' => 'required_if:third_party_cost,yes|numeric|min:0',
            'third_party_costs.*.department' => 'required_if:third_party_cost,yes|string|max:255',
            'third_party_costs.*.file' => 'required_if:third_party_cost,yes|file|mimes:pdf,jpg,jpeg,png,docx,doc|max:10240',
            'po_applicable' => 'nullable|string|in:yes,no',
            'po_number' => 'required_if:po_applicable,yes|nullable|string|max:255',
            'po_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,doc|max:10240',
        ], [
            'third_party_costs.required_if' => 'Please add at least one third party cost.',
            'third_party_costs.*.supplier.required_if' => 'Supplier name is required.',
            'third_party_costs.*.cost.required_if' => 'Cost is required.',
            'third_party_costs.*.department.required_if' => 'Department is required for each third party cost.',
            'third_party_costs.*.file.required_if' => 'Document file is required for each third party cost.',
        ]);

        if ($request->deal_id) {
            $deal = \App\Models\Deal::find($request->deal_id);
            if ($deal && !$deal->canEdit()) {
                abort(403, 'You do not have permission to create an estimate for this deal.');
            }
        }

        if ($request->deal_id && Estimate::where('deal_id', $request->deal_id)->exists()) {
            return back()->withErrors(['deal_id' => 'An estimate already exists for this deal.'])->withInput();
        }

        $referenceNumber = Estimate::generateReferenceNumber();

        $poPath = null;
        if ($request->hasFile('po_document')) {
            $file = $request->file('po_document');
            $poPath = $file->move(public_path('uploads/pos'), time() . '_' . $file->getClientOriginalName());
            $poPath = 'uploads/pos/' . basename($poPath);
        }

        $estimate = Estimate::create([
            'customer_id' => $request->customer_id,
            'user_id' => auth()->id(),
            'reference_number' => $referenceNumber,
            'brand_name' => $request->brand_name,
            'date' => $request->date,
            'status' => 'draft',
            'total_amount' => 0,
            'attention_to' => $request->attention_to,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'address_line_3' => $request->address_line_3,
            'designation' => $request->designation,
            'currency' => $request->currency,
            'heading' => $request->heading,
            'terms' => is_array($request->terms) ? implode(', ', $request->terms) : $request->terms,
            'special_terms' => $request->special_terms,
            'advance_payment' => $request->advance_payment,
            'advance_percentage' => $request->advance_percentage,
            'senior_manager' => $request->senior_manager,
            'additional_notes' => $request->additional_notes,
            'sscl_applicable' => $request->has('sscl_applicable'),
            'vat_applicable' => $request->has('vat_applicable'),
            'deal_id' => $request->deal_id,
            'proforma_invoice' => $request->proforma_invoice ?? 'yes',
            'invoice_type' => $request->invoice_type ?? 'tax_invoice',
            'third_party_cost' => $request->third_party_cost ?? 'no',
            'proforma_percentage' => $request->proforma_percentage,
            'proforma_tax' => $request->proforma_tax ?? 'with_tax',
            'advance_received_amount' => $request->advance_received_amount ?? 0,
            'po_applicable' => $request->po_applicable ?? 'no',
            'po_number' => $request->po_number,
            'po_file_path' => $poPath,
            'date_of_delivery' => $request->date_of_delivery,
            'place_of_supply' => $request->place_of_supply,
        ]);

        $grandTotal = 0;

        $positionCounter = 0;
        foreach ($request->items as $item) {
            $quantity = $item['quantity'];
            $unitPrice = $item['unit_price'];

            // Calculate the taxFactor to back out the base unit price
            $ssclRate = \App\Models\Setting::get('sscl_rate', 2.5641) / 100;
            $vatRate = \App\Models\Setting::get('vat_rate', 15) / 100;
            
            $ssclApplicable = $request->has('sscl_applicable');
            $vatApplicable = $request->has('vat_applicable');

            // The unit price in the request is now the base unit price directly
            $baseUnitPrice = round($unitPrice, 2);
            $amount = round($quantity * $baseUnitPrice, 2);

            // Tax Calculation (Backend Fallback/Verification)
            $sscl = 0;
            $vat = 0;
            if ($ssclApplicable) {
                $sscl = round($amount * $ssclRate, 2);
            }
            if ($vatApplicable) {
                $vat = round(($amount + $sscl) * $vatRate, 2);
            }
            $totalWithVat = round($amount + $vat + $sscl, 2);

            $estimate->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $baseUnitPrice,
                'amount' => $amount,
                'vat_amount' => $vat,
                'sscl_amount' => $sscl,
                'total_with_vat' => $totalWithVat,
                // Use backend counter instead of JS timestamp to prevent INT(11) overflow
                'position' => $positionCounter++,
                'item_heading' => $item['item_heading'] ?? null,
                'locations' => $item['locations'] ?? null,
                'days' => $item['days'] ?? null,
                'department' => $item['department'] ?? null,
                'revenue_category' => $item['revenue_category'] ?? null,
                'type' => $item['type'] ?? 'item',
            ]);

            $grandTotal += $totalWithVat;
        }

        $estimate->update(['total_amount' => round($grandTotal, 2)]);


        // Handle Third Party Costs (Move before Sync)
        $thirdPartyTotal = 0;
        if ($request->third_party_cost === 'yes' && $request->has('third_party_costs')) {
            foreach ($request->third_party_costs as $costData) {
                $filePath = null;
                if (isset($costData['file']) && $costData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $filePath = $costData['file']->store('third_party_costs', 'public');
                }

                $estimate->thirdPartyCosts()->create([
                    'supplier' => $costData['supplier'],
                    'cost' => $costData['cost'],
                    'department' => $costData['department'] ?? null,
                    'file_path' => $filePath,
                ]);
                
                $thirdPartyTotal += (float)$costData['cost'];
            }
        }

        // Sync with Deal if exists
        if ($estimate->deal_id) {
            $deal = \App\Models\Deal::find($estimate->deal_id);
            if ($deal) {
                // Pre-tax total (excluding VAT and SSCL)
                $preTaxTotal = $estimate->items->sum(function($item) {
                    return (float)$item->amount;
                });
                
                $deal->update([
                    'revenue' => $preTaxTotal,
                    'contribution' => $preTaxTotal - $thirdPartyTotal
                ]);
            }
        }

        $this->syncProformaInvoice($estimate);

        $this->logAction("Created estimate: {$estimate->reference_number}", $estimate);
        if ($estimate->deal) {
            $this->notifyStakeholders($estimate->deal, new EstimateCreatedNotification($estimate, $estimate->deal, auth()->user()));
        }

        return redirect()->route('estimates.index')->with('success', 'Estimate created successfully.');
    }

    public function markAsAccepted(Estimate $estimate)
    {
        $oldStatus = $estimate->status;
        $estimate->update(['status' => 'accepted']);
        $this->logAction("Accepted estimate: {$estimate->reference_number}", $estimate);
        if ($estimate->deal) {
            $this->notifyStakeholders($estimate->deal, new EstimateStatusChangedNotification($estimate, $oldStatus, 'accepted', auth()->user()));
        }
        return back()->with('success', 'Estimate marked as accepted.');
    }

    public function markAsRejected(Estimate $estimate)
    {
        $oldStatus = $estimate->status;
        $estimate->update(['status' => 'rejected']);
        $this->logAction("Rejected estimate: {$estimate->reference_number}", $estimate);
        if ($estimate->deal) {
            $this->notifyStakeholders($estimate->deal, new EstimateStatusChangedNotification($estimate, $oldStatus, 'rejected', auth()->user()));
        }
        return back()->with('success', 'Estimate marked as rejected.');
    }

    public function updateStatus(Request $request, Estimate $estimate)
    {
        $user = auth()->user();

        if (!$estimate->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the status of this estimate.');
        }

        $isRestricted = !in_array($user->role, ['Super Admin', 'Management']);

        // Define allowed statuses
        $allowedStatuses = 'draft,approved,accepted,rejected,invoiced,ready_to_invoice';

        // If restricted, they can ONLY set status to ready_to_invoice
        if ($isRestricted && $request->status !== 'ready_to_invoice') {
            return back()->with('error', 'You are only authorized to change status to Ready to Invoice.');
        }

        $request->validate([
            'status' => "required|in:$allowedStatuses"
        ]);

        // Reversion Protection: it cannot be reverted to earlier stages from 'Ready to Invoice' or 'Invoiced'
        $reversionRestricted = ['ready_to_invoice', 'invoiced', 'accepted'];
        $earlierStages = ['draft', 'approved'];

        if ($user->role !== 'Super Admin' && in_array($estimate->status, $reversionRestricted) && in_array($request->status, $earlierStages)) {
            return back()->with('error', 'Estimate status cannot be reverted once it is ' . ucfirst(str_replace('_', ' ', $estimate->status)) . '.');
        }

        $oldStatus = $estimate->status;
        $estimate->update(['status' => $request->status]);

        // Auto-create TempInvoice when status is updated to ready_to_invoice
        if ($request->status === 'ready_to_invoice') {
            $existingTemp = TempInvoice::where('quotation_id', $estimate->id)->first();
            if (!$existingTemp) {
                \Illuminate\Support\Facades\DB::beginTransaction();
                try {
                    $latestTemp = TempInvoice::orderBy('id', 'desc')->first();
                    $sequence = 1;
                    if ($latestTemp && preg_match('/TEMP_(\d+)$/', $latestTemp->temp_invoice_number, $matches)) {
                        $sequence = intval($matches[1]) + 1;
                    }
                    $tempInvoiceNumber = 'TEMP_' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

                    $tempInvoice = TempInvoice::create([
                        'quotation_id' => $estimate->id,
                        'customer_id' => $estimate->customer_id,
                        'temp_invoice_number' => $tempInvoiceNumber,
                        'date' => now()->format('Y-m-d'),
                        'due_date' => now()->addMonth()->format('Y-m-d'),
                        'total_amount' => $estimate->total_amount - ($estimate->advance_received_amount ?? 0),
                        'status' => 'unpaid',
                        'is_proforma' => 0
                    ]);

                    foreach ($estimate->items as $item) {
                        $tempInvoice->items()->create([
                            'description' => $item->description,
                            'type' => $item->type,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'amount' => $item->amount,
                            'sscl_amount' => $item->sscl_amount,
                            'vat_amount' => $item->vat_amount,
                            'total_with_vat' => $item->total_with_vat,
                            'department' => $item->department,
                            'revenue_category' => $item->revenue_category,
                        ]);
                    }

                    \Illuminate\Support\Facades\DB::commit();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    \Illuminate\Support\Facades\Log::error('Failed to auto-create temp invoice: ' . $e->getMessage());
                    return back()->with('error', 'Failed to update status: ' . $e->getMessage());
                }
            }
        }

        $this->logAction("Updated status to {$request->status} for estimate: {$estimate->reference_number}", $estimate);
        if ($estimate->deal) {
            $this->notifyStakeholders($estimate->deal, new EstimateStatusChangedNotification($estimate, $oldStatus, $request->status, auth()->user()));
        }
        $message = "Estimate status updated to " . ucfirst($request->status) . ".";

        if ($request->status === 'ready_to_invoice' || $request->status === 'accepted') {
            return redirect()->route('invoices.ready')->with('success', $message);
        } elseif ($request->status === 'approved' || $request->status === 'invoiced') {
            return redirect()->route('invoices.invoiced')->with('success', $message);
        } elseif ($request->status === 'rejected') {
            return redirect()->route('invoices.rejected')->with('success', $message);
        }

        return redirect()->route('estimates.index')->with('success', $message);
    }

    public function convertToInvoice(Estimate $estimate)
    {
        if (!in_array($estimate->status, ['accepted', 'ready_to_invoice'])) {
            return back()->with('error', 'Only accepted/ready to invoice estimates can be invoiced.');
        }

        // Generate Invoice Number (YYMMM_LDSL_XXXXX)
        $prefix = strtoupper(now()->format('yM')) . '_LDSL_';
        $latestInvoice = \App\Models\Invoice::where('invoice_number', 'LIKE', '%_LDSL_%')
                        ->orderBy('id', 'desc')
                        ->first();

        $sequence = 1;
        if ($latestInvoice && preg_match('/_LDSL_(\d+)$/', $latestInvoice->invoice_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }
        
        $newInvoiceNumber = $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Create Invoice
        $invoice = Invoice::create([
            'quotation_id' => $estimate->id,
            'customer_id' => $estimate->customer_id,
            'invoice_number' => $newInvoiceNumber,
            'date' => now(),
            'due_date' => \Carbon\Carbon::parse($estimate->date)->addMonth(),
            'total_amount' => $estimate->total_amount - ($estimate->advance_received_amount ?? 0),
            'status' => 'unpaid',
            'brand_name' => $estimate->brand_name ?? null,
            'attention_to' => $estimate->attention_to ?? null,
            'address_line_1' => $estimate->address_line_1 ?? null,
            'address_line_2' => $estimate->address_line_2 ?? null,
            'address_line_3' => $estimate->address_line_3 ?? null,
            'designation' => $estimate->designation ?? null,
            'currency' => $estimate->currency ?? 'LKR',
            'heading' => $estimate->heading ?? null,
            'terms' => $estimate->terms ?? null,
            'special_terms' => $estimate->special_terms ?? null,
            'advance_payment' => $estimate->advance_payment ?? null,
            'advance_percentage' => $estimate->advance_percentage ?? 0,
            'advance_received_amount' => $estimate->advance_received_amount ?? 0,
            'invoice_type' => $estimate->invoice_type ?? 'tax_invoice',
            'senior_manager' => $estimate->senior_manager ?? null,
            'sscl_applicable' => $estimate->sscl_applicable ?? false,
            'vat_applicable' => $estimate->vat_applicable ?? false,
            'proforma_percentage' => $estimate->proforma_percentage ?? 50,
            'proforma_tax' => $estimate->proforma_tax ?? 'with_tax',
            'proforma_with_tax' => $estimate->proforma_with_tax ?? true,
            'date_of_delivery' => $estimate->date_of_delivery ?? null,
            'place_of_supply' => $estimate->place_of_supply ?? null,
            'additional_information' => $estimate->additional_notes ?? null,
        ]);

        $estimate->update(['status' => 'invoiced']);

        // Copy Items
        foreach ($estimate->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'type' => $item->type,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'sscl_amount' => $item->sscl_amount,
                'vat_amount' => $item->vat_amount,
                'total_with_vat' => $item->total_with_vat,
                'department' => $item->department,
                'revenue_category' => $item->revenue_category,
            ]);
        }

        $this->logAction("Converted estimate: {$estimate->reference_number} to invoice: {$invoice->invoice_number}", $estimate);
        
        if ($estimate->deal) {
            $this->notifyStakeholders($estimate->deal, new EstimateInvoicedNotification($estimate, $invoice, auth()->user()));
        }

        return redirect()->route('invoices.invoiced')->with('success', 'Estimate converted to Invoice successfully.');
    }




    /**
     * Display the specified resource.
     */
    public function show(Estimate $estimate)
    {
        $estimate->load(['customer', 'items']);
        return view('estimates.show', compact('estimate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $estimate = Estimate::with(['items', 'deal.owner'])->findOrFail($id);

        $user = auth()->user();
        $canEditDeal = $estimate->deal ? $estimate->deal->canEdit($user) : true;
        $readonly = !$canEditDeal;

        if (!$readonly && $user->role !== 'Super Admin') {
            if ($estimate->status === 'ready_to_invoice' || $estimate->status === 'invoiced' || $estimate->status === 'approved') {
                $readonly = true;
            } elseif ($user->role === 'Management' && ($estimate->status === 'invoiced' || $estimate->status === 'approved')) {
                $readonly = true;
            } elseif ($user->role !== 'Management' && $estimate->status !== 'draft') {
                $readonly = true;
            }
        }

        $customers = Customer::all();
        $standardTerms = \App\Models\StandardTerm::all();
        $currencies = \App\Models\SystemCurrency::all();
        $ssclRate = \App\Models\Setting::get('sscl_rate', 2.5641);
        $vatRate = \App\Models\Setting::get('vat_rate', 15);
        $estimateBrands = Estimate::whereNotNull('brand_name')->distinct()->pluck('brand_name');
        $customerBrands = Customer::whereNotNull('brand')->distinct()->pluck('brand');
        $brands = $estimateBrands->concat($customerBrands)->unique()->sort()->values();
        $users = \App\Models\User::whereIn('role', ['HOD', 'Management'])->get();
        return view('estimates.edit', compact('estimate', 'customers', 'standardTerms', 'currencies', 'ssclRate', 'vatRate', 'brands', 'users', 'readonly'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $estimate = Estimate::findOrFail($id);

        $user = auth()->user();
        $canEditDeal = $estimate->deal ? $estimate->deal->canEdit($user) : true;
        if (!$canEditDeal && $user->role !== 'Super Admin' && $user->role !== 'Management') {
             abort(403, 'You do not have permission to edit this estimate.');
        }

        if ($user->role !== 'Super Admin') {
            if ($estimate->status === 'ready_to_invoice' || $estimate->status === 'invoiced' || $estimate->status === 'approved') {
                abort(403, 'This estimate is locked because it is ready to invoice, approved, or already invoiced.');
            }
            if ($user->role === 'Management' && ($estimate->status === 'invoiced' || $estimate->status === 'approved')) {
                abort(403, 'Management cannot edit Invoiced or Approved estimates.');
            } elseif ($user->role !== 'Management' && $estimate->status !== 'draft') {
                abort(403, 'You can only edit Draft estimates.');
            }
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
            'third_party_costs' => 'required_if:third_party_cost,yes|array',
            'third_party_costs.*.supplier' => 'required_if:third_party_cost,yes|string|max:255',
            'third_party_costs.*.cost' => 'required_if:third_party_cost,yes|numeric|min:0',
            'third_party_costs.*.department' => 'required_if:third_party_cost,yes|string|max:255',
            'third_party_costs.*.file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,docx,doc',
                'max:10240',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->third_party_cost === 'yes') {
                        $index = explode('.', $attribute)[1];
                        $costData = $request->third_party_costs[$index];
                        $costId = $costData['id'] ?? null;
                        
                        $hasExisting = false;
                        if ($costId) {
                            $hasExisting = \App\Models\ThirdPartyCost::where('id', $costId)->whereNotNull('file_path')->exists();
                        }
                        
                        if (!$hasExisting && !$request->hasFile("third_party_costs.$index.file")) {
                            $fail('The document file is required for each third party cost.');
                        }
                    }
                }
            ],
            'po_applicable' => 'nullable|string|in:yes,no',
            'po_number' => 'required_if:po_applicable,yes|nullable|string|max:255',
            'po_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,doc|max:10240',
        ], [
            'third_party_costs.required_if' => 'Please add at least one third party cost.',
            'third_party_costs.*.supplier.required_if' => 'Supplier name is required.',
            'third_party_costs.*.cost.required_if' => 'Cost is required.',
            'third_party_costs.*.department.required_if' => 'Department is required for each third party cost.',
        ]);

        $poPath = $estimate->po_file_path;
        if ($request->hasFile('po_document')) {
            // Delete old file if exists
            if ($poPath && file_exists(public_path($poPath))) {
                unlink(public_path($poPath));
            }
            $file = $request->file('po_document');
            $poPath = $file->move(public_path('uploads/pos'), time() . '_' . $file->getClientOriginalName());
            $poPath = 'uploads/pos/' . basename($poPath);
        }

        $estimate->update([
            'customer_id' => $request->customer_id,
            'brand_name' => $request->brand_name,
            'date' => $request->date,
            'attention_to' => $request->attention_to,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'address_line_3' => $request->address_line_3,
            'designation' => $request->designation,
            'currency' => $request->currency,
            'heading' => $request->heading,
            'terms' => is_array($request->terms) ? implode(', ', $request->terms) : $request->terms,
            'special_terms' => $request->special_terms,
            'advance_payment' => $request->advance_payment,
            'advance_percentage' => $request->advance_percentage,
            'senior_manager' => $request->senior_manager,
            'additional_notes' => $request->additional_notes,
            'sscl_applicable' => $request->has('sscl_applicable'),
            'vat_applicable' => $request->has('vat_applicable'),
            'proforma_invoice' => $request->proforma_invoice ?? 'yes',
            'invoice_type' => $request->invoice_type ?? 'tax_invoice',
            'third_party_cost' => $request->third_party_cost ?? 'no',
            'proforma_percentage' => $request->proforma_percentage,
            'proforma_tax' => $request->proforma_tax ?? 'with_tax',
            'advance_received_amount' => $request->advance_received_amount ?? 0,
            'po_applicable' => $request->po_applicable ?? 'no',
            'po_number' => $request->po_number,
            'po_file_path' => $poPath,
            'date_of_delivery' => $request->date_of_delivery,
            'place_of_supply' => $request->place_of_supply,
        ]);

        // Delete existing items and recreate
        $estimate->items()->delete();

        $grandTotal = 0;

        $positionCounter = 0;
        foreach ($request->items as $item) {
            $quantity = $item['quantity'];
            $unitPrice = $item['unit_price'];

            // Calculate the taxFactor to back out the base unit price
            $ssclRate = \App\Models\Setting::get('sscl_rate', 2.5641) / 100;
            $vatRate = \App\Models\Setting::get('vat_rate', 15) / 100;
            
            $ssclApplicable = $request->has('sscl_applicable');
            $vatApplicable = $request->has('vat_applicable');

            // The unit price in the request is now the base unit price directly
            $baseUnitPrice = round($unitPrice, 2);
            $amount = round($quantity * $baseUnitPrice, 2);

            // Tax Calculation (Backend Fallback/Verification)
            $sscl = 0;
            $vat = 0;
            if ($ssclApplicable) {
                $sscl = round($amount * $ssclRate, 2);
            }
            if ($vatApplicable) {
                $vat = round(($amount + $sscl) * $vatRate, 2);
            }
            $totalWithVat = round($amount + $vat + $sscl, 2);

            $estimate->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $baseUnitPrice,
                'amount' => $amount,
                'vat_amount' => $vat,
                'sscl_amount' => $sscl,
                'total_with_vat' => $totalWithVat,
                // Use backend counter instead of JS timestamp to prevent INT(11) overflow
                'position' => $positionCounter++,
                'item_heading' => $item['item_heading'] ?? null,
                'locations' => $item['locations'] ?? null,
                'days' => $item['days'] ?? null,
                'department' => $item['department'] ?? null,
                'revenue_category' => $item['revenue_category'] ?? null,
                'type' => $item['type'] ?? 'item',
            ]);

            $grandTotal += $totalWithVat;
        }

        $estimate->update(['total_amount' => round($grandTotal, 2)]);


        // Handle Third Party Costs (Move before Sync)
        $existingCostIds = $estimate->thirdPartyCosts()->pluck('id')->toArray();
        $submittedCostIds = [];
        $thirdPartyTotal = 0;

        if ($request->third_party_cost === 'yes' && $request->has('third_party_costs')) {
            foreach ($request->third_party_costs as $costData) {
                $costId = $costData['id'] ?? null;
                $filePath = null;

                // Handle file upload
                if (isset($costData['file']) && $costData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $filePath = $costData['file']->store('third_party_costs', 'public');
                }

                if ($costId && in_array($costId, $existingCostIds)) {
                    // Update existing
                    $cost = \App\Models\ThirdPartyCost::find($costId);
                    $updateData = [
                        'supplier' => $costData['supplier'],
                        'cost' => $costData['cost'],
                        'department' => $costData['department'] ?? null,
                    ];
                    
                    if ($filePath) {
                        // Delete old file if new one uploaded
                        if ($cost->file_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($cost->file_path);
                        }
                        $updateData['file_path'] = $filePath;
                    }
                    
                    $cost->update($updateData);
                    $submittedCostIds[] = $costId;
                    $thirdPartyTotal += (float)$costData['cost'];
                } else {
                    // Create new
                    $newCost = $estimate->thirdPartyCosts()->create([
                        'supplier' => $costData['supplier'],
                        'cost' => $costData['cost'],
                        'department' => $costData['department'] ?? null,
                        'file_path' => $filePath,
                    ]);
                    $submittedCostIds[] = $newCost->id;
                    $thirdPartyTotal += (float)$costData['cost'];
                }
            }
        }

        // Delete costs not in submission
        $toDelete = array_diff($existingCostIds, $submittedCostIds);
        foreach ($toDelete as $idToDelete) {
            $costToDelete = \App\Models\ThirdPartyCost::find($idToDelete);
            if ($costToDelete->file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($costToDelete->file_path);
            }
            $costToDelete->delete();
        }

        // Sync with Deal if exists
        if ($estimate->deal_id) {
            $deal = \App\Models\Deal::find($estimate->deal_id);
            if ($deal) {
                // Pre-tax total (excluding VAT and SSCL)
                $preTaxTotal = $estimate->items->sum(function($item) {
                    return (float)$item->amount;
                });
                
                $deal->update([
                    'revenue' => $preTaxTotal,
                    'contribution' => $preTaxTotal - $thirdPartyTotal
                ]);
            }
        }

        $this->syncProformaInvoice($estimate);
        $this->syncTaxInvoices($estimate);

        $this->logAction("Updated estimate: {$estimate->reference_number}", $estimate);
        if ($estimate->deal) {
            $this->notifyStakeholders($estimate->deal);
        }

        return redirect()->route('estimates.show', $estimate->id)->with('success', 'Estimate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estimate $estimate)
    {
        $ref = $estimate->reference_number;
        $estimate->items()->delete();
        $estimate->delete();
        $this->logAction("Deleted estimate: {$ref}");

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted successfully.');
    }

    /**
     * Synchronize the proforma invoice status based on Estimate settings.
     */
    private function syncProformaInvoice(Estimate $estimate)
    {
        if ($estimate->proforma_invoice === 'yes') {
            $existingProforma = Invoice::where('quotation_id', $estimate->id)->where('is_proforma', true)->first();
            
            if ($existingProforma) {
                // Delete old items and update
                $existingProforma->items()->delete();
                $invoice = $existingProforma;
                $invoice->update([
                    'total_amount' => $estimate->total_amount,
                    'date' => now(), // Or $estimate->date
                    // 'due_date' => now()->addDays(30),
                    'brand_name' => $estimate->brand_name ?? null,
                    'attention_to' => $estimate->attention_to ?? null,
                    'address_line_1' => $estimate->address_line_1 ?? null,
                    'address_line_2' => $estimate->address_line_2 ?? null,
                    'address_line_3' => $estimate->address_line_3 ?? null,
                    'designation' => $estimate->designation ?? null,
                    'currency' => $estimate->currency ?? 'LKR',
                    'heading' => $estimate->heading ?? null,
                    'terms' => $estimate->terms ?? null,
                    'special_terms' => $estimate->special_terms ?? null,
                    'advance_payment' => $estimate->advance_payment ?? null,
                    'advance_percentage' => $estimate->advance_percentage ?? 0,
                    'advance_received_amount' => $estimate->advance_received_amount ?? 0,
                    'invoice_type' => $estimate->invoice_type ?? 'tax_invoice',
                    'senior_manager' => $estimate->senior_manager ?? null,
                    'sscl_applicable' => $estimate->sscl_applicable ?? false,
                    'vat_applicable' => $estimate->vat_applicable ?? false,
                    'proforma_percentage' => $estimate->proforma_percentage ?? 50,
                    'proforma_tax' => $estimate->proforma_tax ?? 'with_tax',
                    'proforma_with_tax' => $estimate->proforma_with_tax ?? true,
                    'date_of_delivery' => $estimate->date_of_delivery ?? null,
                    'place_of_supply' => $estimate->place_of_supply ?? null,
                    'additional_information' => $estimate->additional_notes ?? null,
                ]);
            } else {
                // Generate Proforma Invoice Number (PROINV_YY_XXXX)
                $prefix = 'PROINV_' . now()->format('y') . '_';
                $latestInvoice = \App\Models\Invoice::where('invoice_number', 'LIKE', $prefix . '%')
                                ->orderBy('id', 'desc')
                                ->first();

                $sequence = 1;
                if ($latestInvoice && preg_match('/_(\d+)$/', $latestInvoice->invoice_number, $matches)) {
                    $sequence = intval($matches[1]) + 1;
                }
                $newInvoiceNumber = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

                // Create Proforma Invoice
                $invoice = Invoice::create([
                    'quotation_id' => $estimate->id,
                    'customer_id' => $estimate->customer_id,
                    'invoice_number' => $newInvoiceNumber,
                    'date' => now(),
                    'due_date' => \Carbon\Carbon::parse($estimate->date)->addMonth(),
                    'total_amount' => $estimate->total_amount,
                    'status' => 'unpaid',
                    'is_proforma' => true,
                    'brand_name' => $estimate->brand_name ?? null,
                    'attention_to' => $estimate->attention_to ?? null,
                    'address_line_1' => $estimate->address_line_1 ?? null,
                    'address_line_2' => $estimate->address_line_2 ?? null,
                    'address_line_3' => $estimate->address_line_3 ?? null,
                    'designation' => $estimate->designation ?? null,
                    'currency' => $estimate->currency ?? 'LKR',
                    'heading' => $estimate->heading ?? null,
                    'terms' => $estimate->terms ?? null,
                    'special_terms' => $estimate->special_terms ?? null,
                    'advance_payment' => $estimate->advance_payment ?? null,
                    'advance_percentage' => $estimate->advance_percentage ?? 0,
                    'advance_received_amount' => $estimate->advance_received_amount ?? 0,
                    'invoice_type' => $estimate->invoice_type ?? 'tax_invoice',
                    'senior_manager' => $estimate->senior_manager ?? null,
                    'sscl_applicable' => $estimate->sscl_applicable ?? false,
                    'vat_applicable' => $estimate->vat_applicable ?? false,
                    'proforma_percentage' => $estimate->proforma_percentage ?? 50,
                    'proforma_tax' => $estimate->proforma_tax ?? 'with_tax',
                    'proforma_with_tax' => $estimate->proforma_with_tax ?? true,
                    'date_of_delivery' => $estimate->date_of_delivery ?? null,
                    'place_of_supply' => $estimate->place_of_supply ?? null,
                    'additional_information' => $estimate->additional_notes ?? null,
                ]);
            }
            
            // Copy Items
            foreach ($estimate->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                    'sscl_amount' => $item->sscl_amount,
                    'vat_amount' => $item->vat_amount,
                    'total_with_vat' => $item->total_with_vat,
                ]);
            }
        } else {
            // Delete existing proforma if it was changed to 'no'
            Invoice::where('quotation_id', $estimate->id)->where('is_proforma', true)->delete();
        }
    }

    /**
     * Synchronize the regular tax invoices based on Estimate updates.
     */
    private function syncTaxInvoices(Estimate $estimate)
    {
        $taxInvoices = Invoice::where('quotation_id', $estimate->id)->where('is_proforma', false)->get();
        
        foreach ($taxInvoices as $invoice) {
            // Update Invoice Totals and details
            $invoice->update([
                'customer_id' => $estimate->customer_id,
                'total_amount' => $estimate->total_amount - ($estimate->advance_received_amount ?? 0),
            ]);

            // Sync Items
            $invoice->items()->delete();
            foreach ($estimate->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                    'sscl_amount' => $item->sscl_amount,
                    'vat_amount' => $item->vat_amount,
                    'total_with_vat' => $item->total_with_vat,
                    'department' => $item->department,
                    'revenue_category' => $item->revenue_category,
                ]);
            }
        }
    }
}
