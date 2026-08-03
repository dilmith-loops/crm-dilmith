<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Traits\LogsActivity;

class CustomerController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'brand' => 'required|string|max:255',
            'telephone' => 'required|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'fax' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'business_registration_number' => 'nullable|string|max:255',
            'primary_contact_name' => 'nullable|string|max:255',
            'primary_contact_designation' => 'nullable|string|max:255',
            'primary_contact_mobile' => 'required|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'primary_contact_office' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:255',
            'primary_contact_email' => 'required|email|max:255',
            'finance_contact_name' => 'nullable|string|max:255',
            'finance_contact_designation' => 'nullable|string|max:255',
            'finance_contact_mobile' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'finance_contact_office' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:255',
            'finance_contact_email' => 'nullable|email|max:255',
            'customer_tax_number' => 'nullable|string|max:255',
            'customer_vat_registration_number' => 'nullable|string|max:255',
            'customer_suspended_vat_registration_number' => 'nullable|string|max:255',
            'approved_credit_period' => 'nullable|string|max:255',
            'approved_credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create($validated);
        $this->logAction("Created customer: {$customer->name}", $customer);

        // Notify Super Admin
        $admins = \App\Models\User::where('role', 'Super Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\CustomerActionNotification($customer, 'added', auth()->user()));
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403, 'Unauthorized action.');
        }

        $customer = Customer::findOrFail($id);
        $name = $customer->name;
        
        // Deleting customer will cascade delete quotations and invoices due to DB constraints
        $customer->delete();
        
        $this->logAction("Deleted customer: {$name}");

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    // Admin Review Methods
    public function reviewRequest($requestId)
    {
        $request = \App\Models\CustomerUpdateRequest::with(['customer', 'user'])->findOrFail($requestId);
        return view('customers.review', compact('request'));
    }

    public function approveRequest($requestId)
    {
        $request = \App\Models\CustomerUpdateRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            return back()->with('error', 'Request already processed.');
        }

        $customer = $request->customer;
        $customer->update($request->data);

        $request->update(['status' => 'approved']);
        $this->logAction("Approved customer update for: {$customer->name}", $customer);

        // Notify User
        if ($request->user) {
            $request->user->notify(new \App\Notifications\CustomerUpdateResultNotification($request, 'approved'));
        }

        return redirect()->route('customers.index')->with('success', 'Customer update approved and applied.');
    }

    public function rejectRequest($requestId)
    {
        $request = \App\Models\CustomerUpdateRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            return back()->with('error', 'Request already processed.');
        }

        $request->update(['status' => 'rejected']);
        $this->logAction("Rejected customer update for: {$request->customer->name}", $request->customer);

        // Notify User
        if ($request->user) {
            $request->user->notify(new \App\Notifications\CustomerUpdateResultNotification($request, 'rejected'));
        }

        return redirect()->route('customers.index')->with('success', 'Customer update rejected.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'brand' => 'required|string|max:255',
            'telephone' => 'required|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'fax' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'business_registration_number' => 'nullable|string|max:255',
            'primary_contact_name' => 'nullable|string|max:255',
            'primary_contact_designation' => 'nullable|string|max:255',
            'primary_contact_mobile' => 'required|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'primary_contact_office' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:255',
            'primary_contact_email' => 'required|email|max:255',
            'finance_contact_name' => 'nullable|string|max:255',
            'finance_contact_designation' => 'nullable|string|max:255',
            'finance_contact_mobile' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:20',
            'finance_contact_office' => 'nullable|regex:/^[0-9\s\+\-\(\)]+$/|max:255',
            'finance_contact_email' => 'nullable|email|max:255',
            'customer_tax_number' => 'nullable|string|max:255',
            'customer_vat_registration_number' => 'nullable|string|max:255',
            'customer_suspended_vat_registration_number' => 'nullable|string|max:255',
            'approved_credit_period' => 'nullable|string|max:255',
            'approved_credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update($validated);
        $this->logAction("Updated customer: {$customer->name}", $customer);

        // Notify Super Admin if done by a non-admin
        if (auth()->user()->role !== 'Super Admin') {
            $admins = \App\Models\User::where('role', 'Super Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\CustomerActionNotification($customer, 'edited', auth()->user()));
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }
}
