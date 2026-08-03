<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'petty_cash_request_id',
        'expense_category_id',
        'description',
        'amount',
    ];

    public function request()
    {
        return $this->belongsTo(PettyCashRequest::class, 'petty_cash_request_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
