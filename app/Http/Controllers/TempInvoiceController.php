<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TempInvoice;
use App\Models\TempInvoiceItem;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\StandardTerm;
use App\Models\SystemCurrency;
use App\Models\Setting;
use App\Models\User;
use App\Traits\LogsActivity;
use Carbon\Carbon;

class TempInvoiceController extends Controller
{
    use LogsActivity;

    public function edit(TempInvoice $tempInvoice)
    {
        $tempInvoice->load(['items', 'estimate.deal.owner']);

        $user = auth()->user();
        
        // Final processing should be allowed for Super Admin / Management
        $readonly = !in_array($user->role, ['Super Admin', 'Management']);

        $customers = Customer::all();
        $standardTerms = StandardTerm::all();
        $currencies = SystemCurrency::all();
        $ssclRate = Setting::get('sscl_rate', 2.5641);
        $vatRate = Setting::get('vat_rate', 15);
        $estimateBrands = Estimate::whereNotNull('brand_name')->distinct()->pluck('brand_name');
        $customerBrands = Customer::whereNotNull('brand')->distinct()->pluck('brand');
        $brands = $estimateBrands->concat($customerBrands)->unique()->sort()->values();
        $users = User::whereIn('role', ['HOD', 'Management'])->get();

        return view('temp-invoices.edit', compact(
            'tempInvoice',
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

    public function update(Request $request, TempInvoice $tempInvoice)
    {
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
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Update the TempInvoice
            $tempInvoice->update([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'due_date' => Carbon::parse($request->date)->addMonth()->format('Y-m-d'),
                'is_proforma' => ($request->proforma_invoice === 'yes') ? 1 : 0,
            ]);

            // 2. Delete and recreate TempInvoiceItems
            $tempInvoice->items()->delete();
            $grandTotal = 0;
            $positionCounter = 0;

            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];

                $ssclRate = Setting::get('sscl_rate', 2.5641) / 100;
                $vatRate = Setting::get('vat_rate', 15) / 100;
                
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

                $tempInvoice->items()->create([
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

            // Update total amount on the temp invoice
            $tempInvoice->update([
                'total_amount' => $grandTotal - ($request->advance_received_amount ?? 0)
            ]);

            // 3. Compile to final Invoice
            $invoiceDate = Carbon::parse($request->date);
            $prefix = strtoupper($invoiceDate->format('yM')) . '_LDSL_';
            
            $latestInvoice = Invoice::where('invoice_number', 'LIKE', '%_LDSL_%')
                            ->orderBy('id', 'desc')
                            ->first();

            $sequence = 1;
            if ($latestInvoice && preg_match('/_LDSL_(\d+)$/', $latestInvoice->invoice_number, $matches)) {
                $sequence = intval($matches[1]) + 1;
            }
            
            $newInvoiceNumber = $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);

            $estimate = $tempInvoice->estimate;

            $finalInvoice = Invoice::create([
                'quotation_id' => $tempInvoice->quotation_id,
                'customer_id' => $tempInvoice->customer_id,
                'invoice_number' => $newInvoiceNumber,
                'date' => $tempInvoice->date,
                'due_date' => $tempInvoice->due_date,
                'total_amount' => $tempInvoice->total_amount,
                'status' => 'unpaid',
                'is_proforma' => $tempInvoice->is_proforma,
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

            if ($estimate) {
                $estimate->update(['status' => 'invoiced']);
            }

            // Copy items to invoice_items
            foreach ($tempInvoice->items as $tItem) {
                $finalInvoice->items()->create([
                    'description' => $tItem->description,
                    'type' => $tItem->type,
                    'quantity' => $tItem->quantity,
                    'unit_price' => $tItem->unit_price,
                    'amount' => $tItem->amount,
                    'sscl_amount' => $tItem->sscl_amount,
                    'vat_amount' => $tItem->vat_amount,
                    'total_with_vat' => $tItem->total_with_vat,
                    'department' => $tItem->department,
                    'revenue_category' => $tItem->revenue_category,
                ]);
            }

            // 4. Log Action
            $this->logAction("Processed temp invoice {$tempInvoice->temp_invoice_number} into invoice: {$finalInvoice->invoice_number}", $tempInvoice->estimate);

            // 5. Delete Temp Invoice
            $tempInvoice->delete();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('invoices.index')->with('success', 'Invoice generated and processed successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to update and process temp invoice: ' . $e->getMessage());
            return back()->with('error', 'Failed to update and process invoice: ' . $e->getMessage())->withInput();
        }
    }
}
