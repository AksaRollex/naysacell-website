<?php

namespace Database\Seeders;

use App\Models\User;
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
            'address' => 'Jl. Raya Surabaya',
        ])->assignRole('admin');
        User::create([
            'name' => 'user',
            'email' => 'ramsimw8@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '085643217672',
            'address' => 'Jl. Raya Sambiarum Lor',
        ])->assignRole('user');
    }
}
