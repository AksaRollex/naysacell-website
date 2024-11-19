<?php

namespace Database\Seeders;

use App\Models\TransactionModel;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {

        TransactionModel::create([
            'transaction_code' => 'INV-BL-24-11-000000001',
            'transaction_date' => '2024-11-19',
            'transaction_time' => '13:53:38',
            'transaction_type' => 'Prepaid',
            'transaction_provider' => 'BL',
            'transaction_number' => '081234567890',
            'transaction_sku' => 'BL-24-11-000000001',
            'transaction_message' => 'IP Anda tidak kami kenali: 118.99.84.41',
            'transaction_status' => 'Pending',
            'transaction_total' => 0,
            'transaction_user_id' => 1


        ]);
    }
}
