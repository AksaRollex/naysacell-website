<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProvider extends Model
{
    use HasFactory;

    protected $table = 'product_provider';
    protected $fillable = [
        'product_provider',
        'product_provider_photo',
    ];

    public function insert_data($data)
    {
        foreach ($data as $result) {
            $insertData[] = [
                'product_provider' => $result['brand'],
            ];
        }
    }
}
