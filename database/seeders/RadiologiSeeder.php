<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RadiologiSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'dr. Tirta, Sp.Rad',
            'username' => 'rad001', // Gunakan ini untuk login
            'email' => 'radiologi@darsi.com',
            'password' => Hash::make('password'), // Password default
            'role' => 'radiologi',
            'specialization' => 'Sp.Rad',
        ]);
    }
}