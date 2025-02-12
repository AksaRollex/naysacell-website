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
            'email' => 'ramsi.wicaksana@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08536970707',
            'address' => 'Jl. Raya Surabaya',
        ])->assignRole('admin');
        User::create([
            'name' => 'user testing 1',
            'email' => 'ramsimw8@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '085643217622',
            'address' => 'Jl. Raya Sambiarum Lor',
        ])->assignRole('user');
        User::create([
            'name' => 'user testing 2',
            'email' => 'aksarollex333@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '085643217672',
            'address' => 'Jl. Raya Sambiarum Lor',
        ])->assignRole('user');
    }
}
