<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempInvoiceItem extends Model
{
    protected $fillable = [
        'temp_invoice_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'amount',
        'sscl_amount',
        'vat_amount',
        'total_with_vat',
        'department',
        'revenue_category'
    ];

    public function tempInvoice()
    {
        return $this->belongsTo(TempInvoice::class);
    }
}
