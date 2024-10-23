<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->truncate();

        Setting::create([
            'app' => 'NAYSA CELL',
            'description' =>  'Aplikasi NAYSA CELL Topup PPOB',
            'logo' =>  '/media/logo.png',
            'bg_auth' =>  '/media/background.jpg',
            'banner' =>  '/media/banner.jpg',
            'alamat' =>  'Sambiarum Lor, Surabaya',
            'telepon' =>  '085336970707',
            'email' =>  'naysacell@gmail.com',
        ]);
    }
}
