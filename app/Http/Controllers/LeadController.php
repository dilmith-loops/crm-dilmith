<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Estimate;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::latest()->get();
        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'job_description' => 'required|string',
        ]);

        Lead::create($validated);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function markAsDone(Lead $lead)
    {
        $lead->update(['status' => 'done']);

        // Check if customer exists or create new
        $customer = null;
        if ($lead->email) {
            $customer = Customer::where('email', $lead->email)->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name' => $lead->name,
                'email' => $lead->email ?? 'no-email-' . $lead->id . '@example.com', // Placeholder if no email
                'phone' => $lead->phone,
                'address' => 'Generated from Lead: ' . $lead->name,
            ]);
        }

        // Create Draft Estimate
        Estimate::create([
            'customer_id' => $customer->id,
            'reference_number' => Estimate::generateReferenceNumber(),
            'date' => now(),
            'total_amount' => 0, // Pending estimation
            'status' => 'draft',
        ]);

        return redirect()->route('estimates.index')->with('success', 'Lead marked as done. Draft Estimate created!');
    }
}
