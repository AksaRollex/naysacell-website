<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_has_permissions')->delete();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $menuWebsite = ['website', 'setting'];
        $menuUser = ['user', 'user-admin', 'hak-akses', 'user-user'];
        $api = [
            'master-user',
            'master-role',
            'master-product',
            'master-laporan',
            'master-brand-operator',
            'master-digiflazz'
        ];
        $menuMaster = ['master', 'master-brand', 'master-operator-code',];
        $menuProduct = ['produk', 'produk-prabayar', 'produk-pascabayar'];
        $menuPPOB = ['PPOB', 'ppob-internet', 'ppob-pulsapaketdata', 'ppob-pln', 'ppob-pdam', 'ppob-bpjs', 'bpjs-dompetelektronik'];
        $menuLaporan = ['laporan', 'laporan-grafik-penjualan', 'laporan-transaksi-prabayar', 'laporan-transaksi-pascabayar', 'laporan-semua-transaksi'];
        $menuIsiSaldo = [
            'isi-saldo',
            // 'isi-saldo-tarik-tiket',
            'isi-saldo-histori'
        ];
        $menuHistori = ['histori'];

        $permissionsByRole = [
            'admin' => ['dashboard', ...$menuWebsite, ...$menuUser, ...$api, ...$menuMaster, ...$menuProduct, ...$menuPPOB, ...$menuLaporan, ...$menuIsiSaldo],
            'user' => ['dashboard', 'website', ...$menuPPOB, ...$menuIsiSaldo, ...$menuHistori],
        ];

        $insertPermissions = fn($role) => collect($permissionsByRole[$role])
            ->map(function ($name) {
                $check = Permission::whereName($name)->first();

                if (!$check) {
                    return Permission::create([
                        'name' => $name,
                        'guard_name' => 'api',
                    ])->id;
                }

                return $check->id;
            })
            ->toArray();

        $permissionIdsByRole = [
            'admin' => $insertPermissions('admin'),
            'user' => $insertPermissions('user')
        ];

        foreach ($permissionIdsByRole as $role => $permissionIds) {
            $role = Role::whereName($role)->first();

            DB::table('role_has_permissions')
                ->insert(
                    collect($permissionIds)->map(fn($id) => [
                        'role_id' => $role->id,
                        'permission_id' => $id
                    ])->toArray()
                );
        }
    }
}
