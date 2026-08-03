<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'quotation_id',
        'customer_id',
        'invoice_number',
        'date',
        'due_date',
        'total_amount',
        'status',
        'is_proforma',
        'brand_name',
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
        'invoice_type',
        'senior_manager',
        'sscl_applicable',
        'vat_applicable',
        'proforma_percentage',
        'proforma_tax',
        'proforma_with_tax',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'quotation_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
