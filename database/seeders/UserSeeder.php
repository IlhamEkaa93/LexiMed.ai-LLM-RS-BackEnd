<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan database seeds untuk membuat user default RS UNS.
     */
    public function run(): void
    {
        // 1. Membuat/Update akun dr. Ahmad Hidayat (Role: Dokter)
        User::updateOrCreate(
            ['username' => 'dokter_ahmad'], // Mencari berdasarkan username
            [
                'name' => 'dr. Ahmad Hidayat, Sp.PD',
                'email' => 'ahmad@rsuns.ac.id',
                'password' => Hash::make('password'), // Password default: password
                'role' => 'dokter',
            ]
        );

        // 2. Membuat/Update akun Ns. Siti Aminah (Role: Perawat)
        User::updateOrCreate(
            ['username' => 'perawat_siti'],
            [
                'name' => 'Ns. Siti Aminah, S.Kep',
                'email' => 'siti@rsuns.ac.id',
                'password' => Hash::make('password'),
                'role' => 'perawat',
            ]
        );

        // 3. Membuat/Update akun Admin IT (Role: Admin)
        User::updateOrCreate(
            ['username' => 'admin_it'],
            [
                'name' => 'Administrator IT Central',
                'email' => 'admin@rsuns.ac.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}