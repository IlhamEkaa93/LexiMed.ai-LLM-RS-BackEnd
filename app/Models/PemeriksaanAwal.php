<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanAwal extends Model
{
    use HasFactory;

    // Izinkan kolom ini diisi dari API
    protected $fillable = [
        'patient_id',
        'tensi',
        'nadi',
        'suhu',
        'spo2',
        'keluhan_awal',
        'source'
    ];
}