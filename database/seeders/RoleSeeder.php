<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed the database with roles and permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permissions
        $permissions = [
            // User management
            'manage-users',
            'manage-roles',

            // Akademik
            'manage-jadwal',
            'view-jadwal',
            'manage-kegiatan',
            'view-kegiatan',

            // Laporan
            'manage-laporan',
            'view-laporan',
            'create-laporan',
            'edit-laporan',
            'delete-laporan',

            // Nilai & Materi
            'manage-nilai',
            'view-nilai',
            'manage-materi',
            'view-materi',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Role Admin — akses penuh ke semua fitur
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Role Guru — kelola jadwal, kegiatan, laporan, nilai, materi
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $guruRole->givePermissionTo([
            'manage-jadwal',
            'view-jadwal',
            'manage-kegiatan',
            'view-kegiatan',
            'manage-laporan',
            'view-laporan',
            'create-laporan',
            'edit-laporan',
            'delete-laporan',
            'manage-nilai',
            'view-nilai',
            'manage-materi',
            'view-materi',
        ]);

        // Role Student — hanya view
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view-jadwal',
            'view-kegiatan',
            'view-laporan',
            'view-nilai',
            'view-materi',
        ]);
    }
}
