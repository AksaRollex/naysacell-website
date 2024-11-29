<?php

namespace Database\Seeders;

use App\Models\ProductPasca;

use Illuminate\Database\Seeder;

class ProductPascaSeeder extends Seeder
{
    public function run()
    {
        ProductPasca::create([
            "product_name" => "Pln Postpaid",
            "product_category" => "Pascabayar",
            "product_transaction_admin" => 2750,
            "product_transaction_fee" => 1800,
            "product_sku" => "pln",
            "product_provider" => "PLN",
            "product_seller" => "PT. ABC",
            // "buyer_product_status" => true,
            // "seller_product_status" => true,
            // "desc" => "-"
        ]);
        ProductPasca::create([
            "product_name" => "aetra",
            "product_category" => "Pascabayar",
            "product_transaction_admin" => 2000,
            "product_transaction_fee" => 550,
            "product_sku" => "aetra",
            "product_provider" => "PDAM",
            "product_seller" => "Mr Ed",
            // "buyer_product_status" => true,
            // "seller_product_status" => true,
            // "desc" => "Provinsi Jakarta"
        ]);
    }
}
