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
     * FIX MUTLAK: Wajib menyuntikkan kolom 'radiology_*' agar data dari modul PACS/Dokter tidak dibuang Laravel!
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
        'date',
        'radiology_modality', // 🚀 SUNTIK AGAR ORDER RUJUKAN DOKTER BISA MASUK
        'radiology_image',    // 🚀 SUNTIK AGAR UNGGAH FOTO CITRA RADIOLOG BISA MASUK
        'radiology_kesan',    // 🚀 SUNTIK AGAR HASIL EKSPERTISE AI/MANUAL BISA MASUK
        'radiology_doctor'    // 🚀 SUNTIK AGAR NAMA DOKTER SP.RAD BISA MASUK
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