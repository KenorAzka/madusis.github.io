<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'amount_paid', // <-- Pastikan ini ada di dalam fillable
        'payment_date',
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
}