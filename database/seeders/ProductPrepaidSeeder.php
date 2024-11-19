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

        // Ambil data dari API
        $response = Http::withHeaders($header)->post($url . '/price-list', [
            "cmd" => "prepaid",
            "username" => $user,
            "sign" => md5($user . $key . "pricelist"),
        ]);

        $data = json_decode($response->getBody(), true);

        // Simpan data ke dalam tabel menggunakan model
        $productPrepaidModel = new ProductPrepaid();
        $productPrepaidModel->insert_data($data['data']);
    }
}
