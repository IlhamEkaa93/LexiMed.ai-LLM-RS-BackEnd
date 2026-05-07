<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManajemenSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Dr. CEO, MARS',
            'username' => 'dir001',
            'email' => 'direktur@rsuns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'manajemen',
        ]);
    }
}