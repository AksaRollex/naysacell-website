<?php

namespace Database\Seeders;

use App\Models\ProductProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_provider')->truncate();


        ProductProvider::create([
            'provider_name' => 'AXIS',
            'provider_photo' => '/media/products_provider/axis-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'TELKOMSEL',
            'provider_photo' => '/media/products_provider/telkomsel-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'TRI',
            'provider_photo' => '/media/products_provider/3-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'INDOSAT',
            'provider_photo' => '/media/products_provider/indosat-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'BY.U',
            'provider_photo' => '/media/products_provider/by.u-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'DANA',
            'provider_photo' => '/media/products_provider/dana-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'FREEFIRE',
            'provider_photo' => '/media/products_provider/freefire-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'GOPAY',
            'provider_photo' => '/media/products_provider/gopay-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'MLBB',
            'provider_photo' => '/media/products_provider/mlbb-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'OVO',
            'provider_photo' => '/media/products_provider/ovo-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'PERTAMINA',
            'provider_photo' => '/media/products_provider/pertamina-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'PLN',
            'provider_photo' => '/media/products_provider/pln-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'SMARTFREN',
            'provider_photo' => '/media/products_provider/smartfren-logo.png',
        ]);
        ProductProvider::create([
            'provider_name' => 'XL',
            'provider_photo' => '/media/products_provider/xl-logo.png',
        ]);
    }
}
