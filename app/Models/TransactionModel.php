<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use SendinBlue\Client\Model\Order;

class TransactionModel extends Model
{
    use HasFactory;

    protected $table = 'transaction';
    protected $primaryKey = 'id';
    protected $fillable = [
        'transaction_code',
        'transaction_date',
        'transaction_time',
        'transaction_type',
        'transaction_number',
        'transaction_sku',
        'transaction_total',
        'transaction_message',
        'transaction_status',
        'order_status',
        'transaction_user_id',
        'transaction_product',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'transaction_user_id', 'id');
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }


}
