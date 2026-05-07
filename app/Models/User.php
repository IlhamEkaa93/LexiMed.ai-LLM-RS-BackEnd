<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // Tambahkan jika menggunakan Spatie

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'username', // Digunakan sebagai ID Institusi/NIP saat login
        'email',    
        'password',
        'role',     // Untuk membedakan hak akses[cite: 1]
    ];

    /**
     * Kolom yang harus disembunyikan saat dikonversi ke JSON.
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
     * Memastikan Laravel tahu bahwa kita login menggunakan 'username'.
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }
}