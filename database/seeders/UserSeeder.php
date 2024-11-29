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
            'photo' => '/media/logo.png',
        ])->assignRole('admin');
        User::create([
            'name' => 'User 1',
            'email' => 'user1@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '082342342234',
            'address' => 'Jl. Raya Yogyakarta',
            'photo' => '/media/logo.png',
        ])->assignRole('user');
        User::create([
            'name' => 'User 2',
            'email' => 'user2@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '085643217678',
            'address' => 'Jl. Raya Sambiarum Lor',
            'photo' => '/media/logo.png',
        ])->assignRole('user');
    }
}
