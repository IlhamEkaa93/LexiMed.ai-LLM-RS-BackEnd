<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database PostgreSQL Supabase
     */
    protected $table = 'patients';

    /**
     * Karena no_rm adalah string dan bertindak sebagai Primary Key, 
     * kita harus set incrementing ke false.
     */
    protected $primaryKey = 'no_rm';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Kolom yang boleh diisi (Mass Assignment)
     * FIX: Wajib menambahkan 'date' agar nilai parameter penanggalan tidak dibuang Laravel!
     */
    protected $fillable = [
        'no_rm',
        'title',  
        'name',
        'age',
        'gender', 
        'unit',
        'dpjp',
        'status_treatment',
        'date' // 🚀 SUNTIK DISINI AGAR LEGAL DISIMPAN KE SUPABASE
    ];

    /**
     * Casting tipe data otomatis saat dikirim ke frontend
     */
    protected $casts = [
        'age' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}