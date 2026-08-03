<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempInvoice extends Model
{
    protected $fillable = [
        'quotation_id',
        'customer_id',
        'temp_invoice_number',
        'date',
        'due_date',
        'total_amount',
        'status',
        'is_proforma'
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
        return $this->hasMany(TempInvoiceItem::class);
    }
}
