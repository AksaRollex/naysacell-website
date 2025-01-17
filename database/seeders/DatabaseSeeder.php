<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            ProductPrepaidSeeder::class,
            ProductProviderSeeder::class,
            // ProductPascaSeeder::class,
            // TransactionSeeder::class,
            // CodeOperatorSeeder::class
        ]);
    }
}
