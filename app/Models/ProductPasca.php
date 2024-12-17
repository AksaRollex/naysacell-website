<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProductPasca extends Model
{
    use HasFactory;

    protected $table = 'product_pasca';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_name',
        'product_category',
        'product_provider',
        'product_seller',
        'product_transaction_admin',
        'product_transaction_fee',
        'product_sku',
    ];

    // public function scopeFindBySKU($query, $value)
    // {
    //     $query->where('product_sku', $value);
    // }


    public function insert_data($data)
    {
        // Null or empty array check
        if (empty($data)) {
            Log::warning('Attempted to insert empty product data');
            return;
        }

        $insertData = [];
        foreach ($data as $result) {
            $insertData[] = [
                'product_sku' => $result['buyer_sku_code'] ?? null,
                'product_name' => $result['product_name'] ?? null,
                'product_category' => $result['category'] ?? null,
                'product_provider' => $result['brand'] ?? null,
                'product_seller' => $result['seller_name'] ?? null,
                'product_transaction_admin' => $result['admin'] ?? null,
                'product_transaction_fee' => $result['commission'] ?? null,
            ];
        }

        if (!empty($insertData)) {
            self::upsert($insertData, ['product_sku'], [
                'product_name',
                'product_category',
                'product_provider',
                'product_seller',
                'product_transaction_admin',
                'product_transaction_fee'
            ]);
        }
    }
}
