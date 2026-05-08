<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicalData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // Wajib untuk tembak API Groq Cloud / AI

class ClinicalDataController extends Controller
{
    /**
     * 1. SHOW: Menampilkan data klinis terbaru sekaligus profil pasien.
     * Digunakan oleh halaman Data Rekam Medis Dokter.
     * Menjamin Nama, Umur, dan Gender tampil real dari tabel patients.
     */
    public function show($norm)
    {
        try {
            // Ambil data profil pasien dari tabel patients (PENTING untuk Umur & Gender)
            $patientProfile = DB::table('patients')
                ->where('no_rm', $norm)
                ->orWhere('no_rm', 'RM-' . $norm)
                ->first();

            // Ambil data klinis/TTV terbaru dari tabel clinical_data
            $clinicalData = ClinicalData::where('patient_id', $norm)
                ->latest()
                ->first();

            if ($patientProfile || $clinicalData) {
                return response()->json([
                    'success'           => true,
                    // Data Profil Pasien
                    'name'              => $patientProfile->name ?? 'Unknown',
                    'gender'            => $patientProfile->gender ?? '-',
                    'age'               => $patientProfile->age ?? '0',
                    'patient_id'        => $patientProfile->no_rm ?? ($clinicalData->patient_id ?? $norm),
                    
                    // Data Vital Sign (TTV)
                    'blood_pressure'    => $clinicalData->blood_pressure ?? '---/--',
                    'heart_rate'        => $clinicalData->heart_rate ?? '--',
                    'temperature'       => $clinicalData->temperature ?? '--',
                    'oxygen_saturation' => $clinicalData->oxygen_saturation ?? '--',
                    
                    // Data Konten Klinis
                    'raw_content'       => $clinicalData->raw_content ?? '',
                    'ai_summary'        => $clinicalData->ai_summary ?? '',
                    'status'            => $clinicalData->status ?? 'no_data',
                    'created_at'        => $clinicalData ? $clinicalData->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data pasien atau rekam medis tidak ditemukan.'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Error pada ClinicalDataController@show: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. STORE: Menyimpan data pemeriksaan awal atau input klinis dokter.
     */
    public function store(Request $request)
    {
        // Validasi Input
        $validated = $request->validate([
            'patient_id'  => 'required|string',
            'raw_content' => 'required|string',
            'blood_pressure'    => 'nullable|string',
            'heart_rate'        => 'nullable|string',
            'temperature'       => 'nullable|string',
            'oxygen_saturation' => 'nullable|string',
        ]);

        try {
            // Simpan ke database PostgreSQL
            $data = ClinicalData::create([
                'patient_id'        => $validated['patient_id'],
                'blood_pressure'    => $request->blood_pressure ?? '---/--',
                'heart_rate'        => $request->heart_rate ?? '--',
                'temperature'       => $request->temperature ?? '--',
                'oxygen_saturation' => $request->oxygen_saturation ?? '--',
                'raw_content'       => $validated['raw_content'],
                // Catatan: Kolom 'source' DIHAPUS agar tidak error 500 Column Does Not Exist di PostgreSQL
                'status'            => 'draft', // Status draft menunggu verifikasi AI / Dokter
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data klinis berhasil disinkronisasi ke PostgreSQL.',
                'data'    => $data
            ], 201);

        } catch (\Exception $e) {
            Log::error("Gagal simpan ClinicalData: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ke database.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. GENERATE AI: Merapikan draf medis menggunakan GROQ API (Llama 3.3)
     */
    public function generateAI($norm, Request $request)
    {
        $request->validate([
            'raw_text' => 'required|string'
        ]);

        try {
            // Mengambil GROQ API KEY dari .env sesuai dengan file konfigurasi Anda
            $apiKey = env('GROQ_API_KEY', ''); 
            
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Key AI belum dipasang di file .env Laravel (GROQ_API_KEY).'
                ], 400);
            }
            
            // Hit ke API Groq Cloud
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(45)->post('https://api.groq.com/openai/v1/chat/completions', [
                // Menggunakan model tercepat Llama 3.3 70B dari Groq
                'model' => 'llama-3.3-70b-versatile', 
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten dokter profesional di Rumah Sakit. Tugas Anda adalah merapikan catatan medis mentah menjadi narasi ringkasan klinis yang terstruktur, baku, dan mudah dibaca oleh tenaga medis lain. JANGAN gunakan format SOAP (S-O-A-P). Buatlah dalam bentuk 1-2 paragraf naratif medis yang padat dan jelas. Gunakan bahasa Indonesia medis yang baku.'
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Tolong rapikan catatan mentah pasien ini: ' . $request->raw_text
                    ]
                ],
                'temperature' => 0.3 // Temperature rendah agar hasilnya konsisten dan tidak berhalusinasi
            ]);

            if ($response->successful()) {
                $aiResult = $response->json('choices.0.message.content');
                
                // Simpan hasil AI ke Database agar tidak hilang
                $clinicalData = ClinicalData::where('patient_id', $norm)->latest()->first();
                if ($clinicalData) {
                    $clinicalData->update(['ai_summary' => $aiResult]);
                }

                return response()->json([
                    'success' => true, 
                    'summary' => $aiResult
                ], 200);
            }

            // Jika API Groq menolak / limit
            $errorMsg = $response->json('error.message') ?? 'Gagal memanggil AI.';
            return response()->json([
                'success' => false, 
                'message' => 'Server AI Groq merespon error: ' . $errorMsg
            ], 500);

        } catch (\Exception $e) {
            Log::error("AI Error Groq: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Kesalahan sistem saat menghubungi AI: ' . $e->getMessage()
            ], 500);
        }
    }
}