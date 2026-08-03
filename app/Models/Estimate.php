<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    protected $table = 'quotations'; // Keep existing table name

    protected $fillable = [
        'customer_id',
        'user_id',
        'deal_id',
        'brand_name',
        'reference_number',
        'date',
        'total_amount',
        'status',
        'attention_to',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'designation',
        'currency',
        'heading',
        'terms',
        'special_terms',
        'advance_payment',
        'advance_percentage',
        'advance_received_amount',
        'proforma_invoice',
        'invoice_type',
        'third_party_cost',
        'proforma_percentage',
        'proforma_tax',
        'senior_manager',
        'additional_notes',
        'sscl_applicable',
        'vat_applicable',
        'po_applicable',
        'po_number',
        'po_file_path',
        'date_of_delivery',
        'place_of_supply'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'quotation_id');
    }

    public function tempInvoice()
    {
        return $this->hasOne(TempInvoice::class, 'quotation_id');
    }

    public function items()
    {
        return $this->hasMany(EstimateItem::class, 'quotation_id');
    }

    public function thirdPartyCosts()
    {
        return $this->hasMany(ThirdPartyCost::class, 'quotation_id');
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Check if a specific user can edit this estimate.
     */
    public function canEdit($user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        // If it's linked to a deal, follow the deal's edit permissions
        if ($this->deal) {
            return $this->deal->canEdit($user);
        }

        // Fallback for standalone estimates (if any)
        return $this->user_id === $user->id || $user->hasRole('Super Admin') || $user->hasRole('Management');
    }


    public static function generateReferenceNumber()
    {
        $year = date('Y');
        $prefix = "EST/{$year}/";

        $lastEstimate = self::where('reference_number', 'like', $prefix . '%')
            ->orderBy('reference_number', 'desc')
            ->first();

        if (!$lastEstimate) {
            $sequence = 1;
        } else {
            // Extract the number from EST/YYYY/XXXX
            $parts = explode('/', $lastEstimate->reference_number);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
