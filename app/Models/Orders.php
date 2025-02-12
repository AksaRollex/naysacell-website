<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'product_id',
        'quantity',
        'user_id',
        'transaction_id',
    ];

    public function TransactionModel()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductPrepaid::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
