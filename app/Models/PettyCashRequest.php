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
        'issued_at',
        'issued_money_notes',
        'status',
        'hod_rejection_note',
        'admin_rejection_note',
        'signature_path',
        'settlement_signature_path',
        'settled_at',
        'settlement_note',
        'settlement_money_notes',
        'reappeal_count',
    ];

    protected $casts = [
        'is_iou' => 'boolean',
        'issued_at' => 'datetime',
        'settled_at' => 'datetime',
        'issued_money_notes' => 'array',
        'settlement_money_notes' => 'array',
    ];

    public function getIssuedNotesTotalAttribute()
    {
        if (!$this->issued_money_notes || !is_array($this->issued_money_notes)) {
            return 0;
        }
        $n = $this->issued_money_notes;
        return ((int)($n['5000'] ?? 0)) * 5000 +
               ((int)($n['2000'] ?? 0)) * 2000 +
               ((int)($n['1000'] ?? 0)) * 1000 +
               ((int)($n['500'] ?? 0)) * 500 +
               ((int)($n['100'] ?? 0)) * 100 +
               ((int)($n['50'] ?? 0)) * 50 +
               ((int)($n['20'] ?? 0)) * 20 +
               (float)($n['coins'] ?? 0);
    }

    public function getSettlementNotesTotalAttribute()
    {
        if (!$this->settlement_money_notes || !is_array($this->settlement_money_notes)) {
            return 0;
        }
        $n = $this->settlement_money_notes;
        return ((int)($n['5000'] ?? 0)) * 5000 +
               ((int)($n['2000'] ?? 0)) * 2000 +
               ((int)($n['1000'] ?? 0)) * 1000 +
               ((int)($n['500'] ?? 0)) * 500 +
               ((int)($n['100'] ?? 0)) * 100 +
               ((int)($n['50'] ?? 0)) * 50 +
               ((int)($n['20'] ?? 0)) * 20 +
               (float)($n['coins'] ?? 0);
    }

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
