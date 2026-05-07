<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Membuat Role
        $adminRole     = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $dokterRole    = Role::firstOrCreate(['name' => 'dokter', 'guard_name' => 'web']);
        $perawatRole   = Role::firstOrCreate(['name' => 'perawat', 'guard_name' => 'web']);
        $radiologiRole = Role::firstOrCreate(['name' => 'radiologi', 'guard_name' => 'web']);
        $manajemenRole = Role::firstOrCreate(['name' => 'manajemen', 'guard_name' => 'web']);

        // 3. Membuat User Admin IT
        $admin = User::updateOrCreate(
            ['email' => 'admin@darsi.com'],
            [
                'name'     => 'Admin IT DARSI',
                'username' => 'admin_darsi', // Tambahkan username[cite: 1]
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // 4. Membuat User Dokter
        $dokter = User::updateOrCreate(
            ['email' => 'ilham@darsi.com'],
            [
                'name'     => 'dr. Ilham',
                'username' => 'ilham_dokter', // Tambahkan username[cite: 1]
                'password' => Hash::make('password'),
            ]
        );
        $dokter->assignRole($dokterRole);

        // 5. Membuat User Perawat
        $perawat = User::updateOrCreate(
            ['email' => 'perawat@darsi.com'],
            [
                'name'     => 'Suster DARSI',
                'username' => 'perawat_darsi', // Tambahkan username[cite: 1]
                'password' => Hash::make('password'),
            ]
        );
        $perawat->assignRole($perawatRole);

        // Output info ke terminal
        $this->command->info('-----------------------------------------');
        $this->command->info('Seed Berhasil!');
        $this->command->info('Login Dokter: ilham@darsi.com | password');
        $this->command->info('Login Admin : admin@darsi.com | password');
        $this->command->info('-----------------------------------------');
    }
}