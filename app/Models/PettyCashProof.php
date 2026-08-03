<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'petty_cash_request_id',
        'file_path',
        'file_name',
        'file_type',
    ];

    public function request()
    {
        return $this->belongsTo(PettyCashRequest::class, 'petty_cash_request_id');
    }
}
