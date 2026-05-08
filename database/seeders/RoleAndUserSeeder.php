<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Membuat Role Spatie
        $adminRole     = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $dokterRole    = Role::firstOrCreate(['name' => 'dokter', 'guard_name' => 'web']);
        $perawatRole   = Role::firstOrCreate(['name' => 'perawat', 'guard_name' => 'web']);

        // 3. Membuat User Admin IT
        $admin = User::updateOrCreate(
            ['username' => 'admin_darsi'],
            [
                'name'     => 'Admin IT DARSI',
                'email'    => 'admin@darsi.com',
                'password' => Hash::make('password'),
                'role'     => 'admin', // <-- INI YANG BIKIN REACT MENOLAK TADI
                'status'   => 'aktif',
            ]
        );
        $admin->assignRole($adminRole);

        // 4. Membuat User Dokter
        $dokter = User::updateOrCreate(
            ['username' => 'ilham_dokter'],
            [
                'name'     => 'dr. Ilham',
                'email'    => 'ilham@darsi.com',
                'password' => Hash::make('password'),
                'role'     => 'dokter', // <-- DITEGASKAN SEBAGAI DOKTER
                'status'   => 'aktif',
            ]
        );
        $dokter->assignRole($dokterRole);

        // 5. Membuat User Perawat
        $perawat = User::updateOrCreate(
            ['username' => 'perawat_darsi'],
            [
                'name'     => 'Suster DARSI',
                'email'    => 'perawat@darsi.com',
                'password' => Hash::make('password'),
                'role'     => 'perawat', // <-- DITEGASKAN SEBAGAI PERAWAT
                'status'   => 'aktif',
            ]
        );
        $perawat->assignRole($perawatRole);

        // Output info ke terminal
        $this->command->info('-----------------------------------------');
        $this->command->info('Seed Berhasil! Roles sudah sinkron.');
        $this->command->info('Login Dokter: ilham_dokter | password');
        $this->command->info('Login Admin : admin_darsi | password');
        $this->command->info('-----------------------------------------');
    }
}