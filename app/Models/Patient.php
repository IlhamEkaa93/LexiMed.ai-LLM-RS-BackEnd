<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database PostgreSQL
     */
    protected $table = 'patients';

    /**
     * Karena no_rm adalah string dan Primary Key, 
     * kita harus set incrementing ke false.
     */
    protected $primaryKey = 'no_rm';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Kolom yang boleh diisi (Mass Assignment)
     * Harus sinkron dengan file Migration yang baru kamu jalankan.
     */
    protected $fillable = [
        'no_rm',
        'title',  // An, Ny, Tn, Nona
        'name',
        'age',
        'gender', // Laki-Laki / Perempuan
        'unit',
        'dpjp',
        'status_treatment'
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'age' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}