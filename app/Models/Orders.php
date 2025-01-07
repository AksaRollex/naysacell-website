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
        'product_name',
        'product_price',
        'quantity',
        'customer_no',
        'user_id',
        'customer_name',
        'order_status'
    ];

    public function transactions()
    {
        return $this->hasMany(TransactionModel::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductPrepaid::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
