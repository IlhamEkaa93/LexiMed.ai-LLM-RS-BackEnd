<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalData extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database PostgreSQL.
     * Tabel ini menyimpan data rekam medis mentah dan hasil olahan AI.
     */
    protected $table = 'clinical_data';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment).
     * 
     * patient_id  : ID Rekam Medis (NORM) Pasien.
     * source      : Sumber data (contoh: manual, voice, whatsapp via OpenClaw).
     * raw_content : Teks naratif mentah yang diinput oleh dokter atau perawat[cite: 2].
     * ai_summary  : Hasil ringkasan SOAP/SBAR yang dihasilkan oleh model Llama 3.3[cite: 2].
     * status      : Status dokumen (draft = butuh verifikasi, verified = tervalidasi manusia).
     */
    protected $fillable = [
        'patient_id', 
        'source', 
        'raw_content', 
        'ai_summary', 
        'status'
    ];

    /**
     * Casting otomatis untuk kolom database.
     * Menjamin format data konsisten saat diakses oleh sistem[cite: 2].
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status'     => 'string',
    ];

    /**
     * Scope untuk mengambil data yang sudah diverifikasi saja (Verified Only).
     * Digunakan oleh halaman Data Rekam Medis untuk menampilkan histori klinis yang sah[cite: 1].
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope untuk mengambil data draf terbaru.
     * Digunakan oleh modul Ringkasan Medis untuk proses review AI[cite: 1].
     */
    public function scopeLatestDraft($query, $patientId)
    {
        return $query->where('patient_id', $patientId)
                     ->where('status', 'draft')
                     ->latest();
    }
}