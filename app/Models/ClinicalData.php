<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model ClinicalData
 * * Tabel ini bertindak sebagai "Pusat Data Klinis" yang menyimpan:
 * 1. Data Vital Sign (TTV) yang diinput oleh asisten/perawat.
 * 2. Narasi/Keluhan mentah pasien (Raw Content).
 * 3. Hasil ringkasan medis berbasis AI (Llama 3.3).
 */
class ClinicalData extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database PostgreSQL.
     */
    protected $table = 'clinical_data';

    /**
     * Kolom yang dapat diisi melalui Mass Assignment.
     * Sudah mencakup gabungan data TTV Asisten dan Data Klinis AI.
     */
    protected $fillable = [
        'patient_id',        // NORM Pasien (Foreign Key/Link)
        'blood_pressure',    // Tensi / Tekanan Darah (Contoh: 120/80)
        'heart_rate',        // Nadi / Detak Jantung
        'temperature',       // Suhu Tubuh
        'oxygen_saturation', // SpO2 / Saturasi Oksigen
        'source',            // Sumber data: manual, voice, whatsapp
        'raw_content',       // Narasi keluhan mentah atau transkrip voice
        'ai_summary',        // Output ringkasan SOAP dari AI
        'status'             // draft (proses asisten) atau verified (disahkan dokter)
    ];

    /**
     * Casting otomatis untuk memastikan tipe data konsisten saat dikirim ke Frontend.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status'     => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    | Scopes mempermudah query di Controller (Logic Reusable).
    */

    /**
     * Mengambil data yang sudah divalidasi oleh dokter.
     * Digunakan untuk Histori Rekam Medis.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Mengambil draf terbaru (Input asisten yang belum diringkas AI/Dokter).
     */
    public function scopeLatestDraft($query, $patientId)
    {
        return $query->where('patient_id', $patientId)
                     ->where('status', 'draft')
                     ->latest();
    }

    /**
     * Mendapatkan profil klinis lengkap (TTV + Narasi) berdasarkan NORM.
     */
    public function scopeFullProfile($query, $patientId)
    {
        return $query->where('patient_id', $patientId)
                     ->latest();
    }
}