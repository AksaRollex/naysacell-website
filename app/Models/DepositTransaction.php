<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'deposit_code',
        'payment_type',
        'paid_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
