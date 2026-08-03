<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = [
        'type',
        'department',
        'user_id',
        'target_amount'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
