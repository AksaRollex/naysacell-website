<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\ProductPrepaid;

class ProductPrepaidSeeder extends Seeder
{

    public function run()
    {
        $header = [
            'Content-Type' => 'application/json',
        ];

        $url = env('DIGIFLAZ_URL');
        $user = env('DIGIFLAZ_USER');
        $key = env('DIGIFLAZ_MODE') == 'development' ? env('DIGIFLAZ_DEV_KEY') : env('DIGIFLAZ_PROD_KEY');

        $response = Http::withHeaders($header)->post($url . '/price-list', [
            "cmd" => "prepaid",
            "username" => $user,
            "sign" => md5($user . $key . "pricelist"),
        ]);

        $data = json_decode($response->getBody(), true);

        $productPrepaidModel = new ProductPrepaid();
        $productPrepaidModel->insert_data($data['data']);
    }


    // public function run(): void
    // {
    //     ProductPrepaid::create([
    //         'product_name' => 'Pulsa Telkomsel 10000',
    //         'product_desc' =>  'Pulsa 10000',
    //         'product_category' => 'Pulsa',
    //         'product_price' => '10000',
    //         'product_stock' => 'unlimited',
    //         'product_sku' => 'NAYSA',
    //     ]);
    // }
}
