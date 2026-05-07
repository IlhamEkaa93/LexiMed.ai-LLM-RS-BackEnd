<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicalData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClinicalDataController extends Controller
{
    public function store(Request $request)
    {
        // 1. Tambahkan waktu eksekusi
        set_time_limit(120);

        // 2. Validasi Input
        $validated = $request->validate([
            'patient_id'  => 'required|string',
            'raw_content' => 'required|string',
            'source'      => 'nullable|in:manual,whatsapp,voice' 
        ]);

        try {
            // --- OPTIMASI 1: LIMIT TEKS (Mencegah Error 413 / Request Too Large) ---
            // Kita potong konten mentah maksimal 5000 karakter agar tidak melebihi 12.000 TPM Groq
            $limited_content = substr($validated['raw_content'], 0, 5000);

            // Gunakan model yang lebih stabil (atau ganti ke llama-3.1-8b-instant jika kuota mepet)
            $modelId = 'llama-3.3-70b-versatile'; 

            // 3. Request ke Groq Cloud
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type'  => 'application/json',
                ])
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $modelId, 
                    
                    // --- OPTIMASI 2: PROMPT RINGKAS & PARAMETER KETAT ---
                    'messages' => [
                        [
                            'role' => 'system', 
                            'content' => 'Anda asisten medis RS UNS. Ringkas teks naratif menjadi format SOAP. Gunakan bahasa Indonesia medis yang baku. Jangan berikan penjelasan pembuka/penutup.'
                        ],
                        [
                            'role' => 'user', 
                            'content' => "Data Pasien: \n" . $limited_content
                        ]
                    ],
                    'max_tokens' => 1000,   // Batasi panjang jawaban AI agar hemat kuota
                    'temperature' => 0.1,  // Rendah agar AI fokus pada data, bukan berimajinasi
                    'top_p' => 1,
                    // -------------------------------------------------------
                ]);

            // 4. Cek kegagalan API
            if ($response->failed()) {
                $errorDetail = $response->json()['error']['message'] ?? 'Koneksi ke AI terputus.';
                throw new \Exception("Groq Error: " . $errorDetail);
            }

            // Ambil konten ringkasan
            $summary = $response->json()['choices'][0]['message']['content'] ?? 'AI gagal merangkum.';

            // 5. Simpan ke database PostgreSQL
            $data = ClinicalData::updateOrCreate(
                ['patient_id' => $validated['patient_id']], 
                [
                    'source'      => $validated['source'] ?? 'manual',
                    'raw_content' => $validated['raw_content'], // Tetap simpan konten asli yang panjang di DB
                    'ai_summary'  => $summary,
                    'status'      => 'draft'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Ringkasan medis berhasil diproses.',
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            // Catat error ke log file
            Log::error("ClinicalData Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Sistem sibuk atau kuota habis. Silakan coba lagi dalam 1 menit."
            ], 500);
        }
    }
}