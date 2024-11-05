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
        $menuUserMitra = ['mitra', 'mitra-user-admin', 'mitra-user-mitra', 'mitra-hak-akses', 'master-user', 'master-role'];
        $menuMaster = ['master', 'master-brand', 'master-operator-code'];
        $menuProduct = ['produk', 'produk-prabayar', 'produk-pascabayar'];
        $menuPPOB = ['PPOB'];
        $menuLaporan = ['laporan', 'laporan-grafik-penjualan', 'laporan-transaksi-prabayar', 'laporan-transaksi-pascabayar'];
        $menuIsiSaldo = ['isi-saldo', 'isi-saldo-tarik-tiket', 'isi-saldo-histori'];


        $permissionsByRole = [
            'admin' => ['dashboard', ...$menuWebsite, ...$menuUserMitra, ...$menuMaster, ...$menuProduct, ...$menuPPOB, ...$menuLaporan, ...$menuIsiSaldo],
            'mitra' => ['dashboard', 'website' , ...$menuProduct, ...$menuPPOB, ...$menuIsiSaldo],
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
            'mitra' => $insertPermissions('mitra')
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
