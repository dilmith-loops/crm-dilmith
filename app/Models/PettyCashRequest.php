<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'user_id',
        'hod_id',
        'department',
        'job_number',
        'total_amount',
        'is_iou',
        'status',
        'hod_rejection_note',
        'admin_rejection_note',
        'signature_path',
        'settlement_signature_path',
        'settled_at',
        'reappeal_count',
    ];

    protected $casts = [
        'is_iou' => 'boolean',
        'settled_at' => 'datetime',
    ];

    public function isIOU()
    {
        if ($this->is_iou) {
            return true;
        }
        return $this->items()->whereHas('category', function($q) {
            $q->where('name', 'LIKE', '%IOU%');
        })->exists();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    public function items()
    {
        return $this->hasMany(PettyCashItem::class, 'petty_cash_request_id');
    }

    public function proofs()
    {
        return $this->hasMany(PettyCashProof::class, 'petty_cash_request_id');
    }

    public static function generateReferenceNumber()
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $sequence = $last ? ((int) substr($last->reference_number, -4)) + 1 : 1;
        return 'PC-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
