<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\ProductPrepaid;

class ProductPrepaidSeeder extends Seeder
{

    // public function run()
    // {
    //     $header = [
    //         'Content-Type' => 'application/json',
    //     ];

    //     $url = env('DIGIFLAZ_URL');
    //     $user = env('DIGIFLAZ_USER');
    //     $key = env('DIGIFLAZ_MODE') == 'development' ? env('DIGIFLAZ_DEV_KEY') : env('DIGIFLAZ_PROD_KEY');

    //     $response = Http::withHeaders($header)->post($url . '/price-list', [
    //         "cmd" => "prepaid",
    //         "username" => $user,
    //         "sign" => md5($user . $key . "pricelist"),
    //     ]);

    //     $data = json_decode($response->getBody(), true);

    //     $productPrepaidModel = new ProductPrepaid();
    //     $productPrepaidModel->insert_data($data['data']);
    // }


    public function run()
    {
        $providers = ['Telkomsel', 'Axis', 'XL', 'Indosat', 'Smartfren', 'Three', 'by.U'];
        $denominations = [
            10000 => 11500,
            20000 => 21500,
            50000 => 51000,
            100000 => 100500,
        ];

        // Generate SKUs for pulsa products
        foreach ($providers as $provider) {
            foreach ($denominations as $nominal => $price) {
                // Create unique SKU: First 3 letters of provider + P (Pulsa) + Nominal
                $sku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $provider), 0, 3) .
                    'P' .
                    $nominal);

                ProductPrepaid::create([
                    'product_name' => "Pulsa {$provider} {$nominal}",
                    'product_desc' => "Pulsa {$nominal}",
                    'product_category' => 'Pulsa',
                    'product_provider' => $provider,
                    'product_price' => $price,
                    'product_sku' => $sku,
                ]);
            }
        }

        $dataPackages = [
            [
                'size' => '1GB',
                'validity' => '30 hari',
                'price' => 25000,
            ],
            [
                'size' => '2GB',
                'validity' => '30 hari',
                'price' => 35000,
            ],
            [
                'size' => '5GB',
                'validity' => '30 hari',
                'price' => 60000,
            ],
            [
                'size' => '10GB',
                'validity' => '30 hari',
                'price' => 95000,
            ],
        ];

        // Generate SKUs for data packages
        foreach ($providers as $provider) {
            foreach ($dataPackages as $package) {
                // Create unique SKU: First 3 letters of provider + D (Data) + Size without GB
                $size = (int) str_replace('GB', '', $package['size']);
                $sku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $provider), 0, 3) .
                    'D' .
                    str_pad($size, 2, '0', STR_PAD_LEFT));

                ProductPrepaid::create([
                    'product_name' => "Data {$provider} {$package['size']}",
                    'product_desc' => "Paket Data {$package['size']} berlaku {$package['validity']}",
                    'product_category' => 'Data',
                    'product_provider' => $provider,
                    'product_price' => $package['price'],
                    'product_sku' => $sku,
                ]);
            }
        }

        // E-Money products
        $emoneyProviders = ['Shopeepay', 'Gopay', 'OVO', 'DANA'];
        $emoneyDenominations = [
            10000 => 11000,
            25000 => 26000,
            50000 => 51000,
            100000 => 101000,
            150000 => 151500,
            200000 => 201500
        ];

        // Generate SKUs for e-money products
        foreach ($emoneyProviders as $provider) {
            foreach ($emoneyDenominations as $nominal => $price) {
                // Create unique SKU: First 3 letters of provider + E (E-Money) + Nominal
                $sku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $provider), 0, 3) .
                    'E' .
                    $nominal);

                ProductPrepaid::create([
                    'product_name' => "{$provider} {$nominal}",
                    'product_desc' => "Saldo {$provider} {$nominal}",
                    'product_category' => 'E-Money',
                    'product_provider' => $provider,
                    'product_price' => $price,
                    'product_sku' => $sku,
                ]);
            }
        }
    }
}
