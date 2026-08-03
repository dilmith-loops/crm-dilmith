<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyCost extends Model
{
    protected $table = 'quotation_third_party_costs';

    protected $fillable = [
        'quotation_id',
        'supplier',
        'cost',
        'department',
        'file_path',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'quotation_id');
    }
}
