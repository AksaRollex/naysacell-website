<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08123456789',
        ])->assignRole('admin');
        User::create([
            'name' => 'Mitra 1',
            'email' => 'mitra1@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08234234234',
        ])->assignRole('mitra');
    }
}
