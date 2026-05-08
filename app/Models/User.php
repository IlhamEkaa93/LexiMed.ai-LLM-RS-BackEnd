<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // <-- WAJIB AKTIF UNTUK SEEDER & ROLE

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; 

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignment).
     * Pastikan semua kolom baru masuk ke sini agar tidak diblokir oleh Laravel.
     */
    protected $fillable = [
        'name',
        'username', // Digunakan sebagai ID Institusi/NIP saat login
        'email',    
        'password',
        'role',     // Untuk membedakan hak akses (frontend)
        'unit',           
        'status',         
        'specialization'  
    ];

    /**
     * Kolom yang harus disembunyikan saat dikonversi ke JSON/Array.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kolom yang harus dikonversi ke tipe data tertentu (Casting).
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', 
    ];

    /**
     * Helper tambahan:
     * Memastikan Laravel tahu bahwa kita login menggunakan 'username' (bukan email).
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }
}