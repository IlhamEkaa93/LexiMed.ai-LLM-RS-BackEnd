<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicalData;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class ClinicalDataController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Normalisasi no_rm — selalu kembalikan format TANPA prefix "RM-"
    // Contoh: "RM-001" → "001", "001" → "001"
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizeNorm(string $norm): string
    {
        return ltrim(str_replace('RM-', '', $norm), '0') 
            ? ltrim(str_replace('RM-', '', $norm)) 
            : $norm;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Cari Patient dengan toleransi format no_rm (dengan/tanpa "RM-")
    // ─────────────────────────────────────────────────────────────────────────
    private function findPatient(string $norm): ?Patient
    {
        $withPrefix    = str_starts_with($norm, 'RM-') ? $norm : 'RM-' . $norm;
        $withoutPrefix = str_starts_with($norm, 'RM-') ? substr($norm, 3) : $norm;

        return Patient::where('no_rm', $norm)
            ->orWhere('no_rm', $withPrefix)
            ->orWhere('no_rm', $withoutPrefix)
            ->first();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Cari ClinicalData terbaru dengan toleransi format patient_id
    // ─────────────────────────────────────────────────────────────────────────
    private function findLatestClinical(string $norm): ?ClinicalData
    {
        $withPrefix    = str_starts_with($norm, 'RM-') ? $norm : 'RM-' . $norm;
        $withoutPrefix = str_starts_with($norm, 'RM-') ? substr($norm, 3) : $norm;

        return ClinicalData::where('patient_id', $norm)
            ->orWhere('patient_id', $withPrefix)
            ->orWhere('patient_id', $withoutPrefix)
            ->latest()
            ->first();
    }

    /**
     * 1. SHOW: Data klinis terbaru + TTV + status radiologi terkini.
     */
    public function show($norm)
    {
        try {
            $patientProfile = $this->findPatient($norm);
            $clinicalData   = $this->findLatestClinical($norm);

            if ($patientProfile || $clinicalData) {
                return response()->json([
                    'success'              => true,
                    'name'                 => $patientProfile->name ?? 'Unknown',
                    'gender'               => $patientProfile->gender ?? '-',
                    'age'                  => $patientProfile->age ?? '0',
                    'patient_id'           => $patientProfile->no_rm ?? ($clinicalData->patient_id ?? $norm),
                    'blood_pressure'       => $clinicalData->blood_pressure ?? '---/--',
                    'heart_rate'           => $clinicalData->heart_rate ?? '--',
                    'temperature'          => $clinicalData->temperature ?? '--',
                    'oxygen_saturation'    => $clinicalData->oxygen_saturation ?? '--',
                    'raw_content'          => $clinicalData->raw_content ?? '',
                    'ai_summary'           => $clinicalData->ai_summary ?? '',
                    'radiology_modality'   => $clinicalData->radiology_modality ?? null,
                    'radiology_kesan'      => $clinicalData->radiology_kesan ?? null,
                    'radiology_image'      => $clinicalData->radiology_image ?? null,
                    'radiology_doctor'     => $clinicalData->radiology_doctor ?? null,
                    'radiology_updated_at' => $clinicalData?->updated_at?->toIso8601String(),
                    'status'               => $clinicalData->status ?? 'no_data',
                    'created_at'           => $clinicalData?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            Log::error("Error ClinicalDataController@show: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 2. STORE: Input data rekam medis awal oleh dokter poliklinik.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'        => 'required|string',
            'raw_content'       => 'required|string',
            'blood_pressure'    => 'nullable|string',
            'heart_rate'        => 'nullable|string',
            'temperature'       => 'nullable|string',
            'oxygen_saturation' => 'nullable|string',
            'custom_prompt'     => 'nullable|string',
        ]);

        try {
            $data = ClinicalData::create([
                'patient_id'        => $validated['patient_id'],
                'blood_pressure'    => $request->blood_pressure ?? '---/--',
                'heart_rate'        => $request->heart_rate ?? '--',
                'temperature'       => $request->temperature ?? '--',
                'oxygen_saturation' => $request->oxygen_saturation ?? '--',
                'raw_content'       => $validated['raw_content'],
                'date'              => date('Y-m-d'),
                'status'            => 'draft',
                'created_at'        => now(),
            ]);

            if ($request->has('custom_prompt')) {
                $aiRequest = new Request();
                $aiRequest->replace([
                    'raw_text'      => $validated['raw_content'],
                    'custom_prompt' => $request->input('custom_prompt'),
                ]);
                $this->generateAI($validated['patient_id'], $aiRequest);
            }

            return response()->json(['success' => true, 'data' => ClinicalData::find($data->id)], 201);
        } catch (\Exception $e) {
            Log::error("Gagal simpan ClinicalData: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 3. GENERATE AI: Orkestrasi Dual-Engine Berantai
     */
    public function generateAI($norm, Request $request)
    {
        $request->validate(['raw_text' => 'required|string', 'custom_prompt' => 'nullable|string']);

        try {
            $rawText      = $request->raw_text;
            $customPrompt = $request->custom_prompt ?? 'Analisis catatan medis ini.';

            $openClawPath = app_path('Agents/OpenClaw/openclaw_gateway.py');
            $voltaPath    = app_path('Agents/Voltagent/main.py');

            $openClawCmd     = ['python3', $openClawPath, '--text', $rawText, '--prompt', $customPrompt];
            $openClawProcess = new Process($openClawCmd);
            $openClawProcess->setTimeout(30);
            $openClawProcess->run();

            if (!$openClawProcess->isSuccessful()) {
                throw new \Exception("OpenClaw Error: " . $openClawProcess->getErrorOutput());
            }

            $gatewayResultJson = trim($openClawProcess->getOutput());

            $voltaCmd     = ['python3', $voltaPath, '--gateway_json', $gatewayResultJson];
            $voltaProcess = new Process($voltaCmd);
            $voltaProcess->setTimeout(30);
            $voltaProcess->run();

            if ($voltaProcess->isSuccessful()) {
                $aiResult     = trim($voltaProcess->getOutput());
                $clinicalData = $this->findLatestClinical($norm);
                if ($clinicalData) {
                    $clinicalData->update(['ai_summary' => $aiResult]);
                }
                return response()->json(['success' => true, 'summary' => $aiResult], 200);
            }

            throw new \Exception("Voltagent Error: " . $voltaProcess->getErrorOutput());
        } catch (\Exception $e) {
            Log::error("Hybrid AI Pipeline Failure: " . $e->getMessage());
            return $this->generateAIFallbackDirect($norm, $request);
        }
    }

    /**
     * Helper Fallback: Direct Groq API
     */
    private function generateAIFallbackDirect($norm, Request $request)
    {
        $apiKey           = env('GROQ_API_KEY', '');
        $systemInstruction = $request->input('custom_prompt') 
            ?? 'Anda adalah asisten dokter profesional di Rumah Sakit. Rapikan catatan medis mentah menjadi narasi ringkasan klinis yang terstruktur dan baku dalam bahasa Indonesia medis.';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(45)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'       => 'llama-3.3-70b-versatile',
            'messages'    => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user',   'content' => 'Tolong rapikan catatan mentah pasien ini: ' . $request->raw_text],
            ],
            'temperature' => 0.3,
        ]);

        if ($response->successful()) {
            $aiResult     = $response->json('choices.0.message.content');
            $clinicalData = $this->findLatestClinical($norm);
            if ($clinicalData) {
                $clinicalData->update(['ai_summary' => $aiResult]);
            }
            return response()->json(['success' => true, 'summary' => $aiResult], 200);
        }

        return response()->json(['success' => false, 'message' => 'Seluruh core AI terisolasi.'], 500);
    }

    /**
     * 4. SANDBOX EXECUTE: Multi-Agent playground.
     */
    public function sandboxExecute(Request $request)
    {
        $request->validate(['role' => 'required|string', 'system_prompt' => 'required|string', 'raw_text' => 'required|string']);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'    => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $request->system_prompt],
                    ['role' => 'user',   'content' => $request->raw_text],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'status'          => 'success',
                    'active_agent'    => $request->role,
                    'pipeline_output' => ['content' => $response->json('choices.0.message.content')],
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Gagal memproses di AI node.'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'exception', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 5. RADIOLOGY ORDER: Kirim instruksi rujukan dari poliklinik ke unit radiologi.
     *
     * FIX: Pakai findPatient() agar toleran format no_rm (dengan/tanpa "RM-")
     * FIX: Reset radiology_image di patients tabel saat ada order baru
     * FIX: touch() dipanggil agar baris naik ke atas antrean /patients-list
     */
    public function storeRadiologyOrder(Request $request, $norm)
    {
        $request->validate([
            'radiology_modality' => 'required|string',
            'catatan_rujukan'    => 'nullable|string',
        ]);

        try {
            $todayIso     = date('Y-m-d');
            $clinicalData = $this->findLatestClinical($norm);

            if (!$clinicalData) {
                ClinicalData::create([
                    'patient_id'         => $norm,
                    'radiology_modality' => $request->radiology_modality,
                    'raw_content'        => $request->catatan_rujukan ?? 'Permintaan rujukan baru poliklinik',
                    'radiology_kesan'    => null,
                    'radiology_image'    => null,
                    'radiology_doctor'   => null,
                    'date'               => $todayIso,
                    'source'             => 'manual',
                    'status'             => 'draft',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            } else {
                $clinicalData->update([
                    'radiology_modality' => $request->radiology_modality,
                    'radiology_kesan'    => null,
                    'radiology_image'    => null,
                    'radiology_doctor'   => null,
                    'date'               => $todayIso,
                ]);
            }

            // ── FIX UTAMA: Pakai findPatient() agar toleran format RM prefix ──
            $patient = $this->findPatient($norm);

            if ($patient) {
                $patient->update([
                    'radiology_modality' => $request->radiology_modality,
                    'radiology_image'    => null, // ⚠️ Reset agar muncul di antrean PACS
                    'radiology_kesan'    => null,
                    'radiology_doctor'   => null,
                    'date'               => $todayIso, // FIX: Update date agar patients-list query cocok
                ]);
                // Paksa updated_at naik ke atas antrean harian
                $patient->touch();

                Log::info("Radiology order synced to patients table: no_rm={$patient->no_rm}, modality={$request->radiology_modality}");
            } else {
                // ⚠️ WARNING: Patient tidak ditemukan — data hanya masuk clinical_data, tidak ke patients
                Log::warning("storeRadiologyOrder: Patient not found for norm={$norm}. Radiology order saved to clinical_data only.");
            }

            return response()->json([
                'success' => true,
                'message' => 'Instruksi rujukan berhasil disimpan.',
                'patient_synced' => $patient !== null, // Feedback ke frontend
            ], 200);
        } catch (\Exception $e) {
            Log::error("Gagal storeRadiologyOrder: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Ambil user_id integer yang aman untuk kolom BIGINT di audit_logs.
     */
    private function getSafeUserId(): int
    {
        $user = auth()->user();
        return $user ? (int) $user->id : 1;
    }

    /**
     * 6. VERIFY — Gerbang tunggal untuk dua sumber timeline
     */
    public function verify(Request $request, $norm)
    {
        DB::beginTransaction();
        try {
            $todayIso        = date('Y-m-d');
            $lastDraft       = $this->findLatestClinical($norm);
            $isRadiologPath  = $request->has('radiology_kesan') || $request->has('base64_image');
            $savedImagePath  = $lastDraft?->radiology_image;

            if ($request->filled('base64_image')) {
                $base64Data  = $request->base64_image;
                $mime        = $request->input('image_mime', 'image/jpeg');
                $extension   = ($mime === 'image/png') ? 'png' : 'jpg';
                $imageDecoded = base64_decode($base64Data);
                $fileName    = time() . '_pacs_' . $norm . '.' . $extension;
                $publicPath  = public_path('storage/radiology/');

                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0777, true);
                }

                file_put_contents($publicPath . $fileName, $imageDecoded);
                $savedImagePath = asset('storage/radiology/' . $fileName);
            }

            if ($isRadiologPath) {
                $insertPayload = [
                    'patient_id'         => $norm,
                    'blood_pressure'     => $lastDraft?->blood_pressure ?? '---/--',
                    'heart_rate'         => $lastDraft?->heart_rate ?? '--',
                    'temperature'        => $lastDraft?->temperature ?? '--',
                    'oxygen_saturation'  => $lastDraft?->oxygen_saturation ?? '--',
                    'raw_content'        => $request->input('final_summary', $lastDraft?->raw_content ?? 'Pemeriksaan penunjang.'),
                    'ai_summary'         => $request->input('final_summary', $lastDraft?->ai_summary ?? ''),
                    'radiology_modality' => $request->input('radiology_modality', $lastDraft?->radiology_modality ?? 'Toraks X-Ray'),
                    'radiology_kesan'    => $request->input('radiology_kesan'),
                    'radiology_doctor'   => $request->input('radiology_doctor', 'Dr. Radiolog'),
                    'radiology_image'    => $savedImagePath,
                    'date'               => $todayIso,
                    'status'             => 'verified',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                // Sync ke tabel patients agar pasien hilang dari antrean PACS setelah diverifikasi
                $patient = $this->findPatient($norm);
                if ($patient) {
                    $patient->update([
                        'radiology_image'  => $savedImagePath,
                        'radiology_kesan'  => $request->input('radiology_kesan'),
                        'radiology_doctor' => $request->input('radiology_doctor', 'Dr. Radiolog'),
                    ]);
                }
            } else {
                $insertPayload = [
                    'patient_id'         => $norm,
                    'blood_pressure'     => $lastDraft?->blood_pressure ?? '---/--',
                    'heart_rate'         => $lastDraft?->heart_rate ?? '--',
                    'temperature'        => $lastDraft?->temperature ?? '--',
                    'oxygen_saturation'  => $lastDraft?->oxygen_saturation ?? '--',
                    'raw_content'        => $lastDraft?->raw_content ?? 'Catatan klinis.',
                    'ai_summary'         => $request->input('ai_summary', ''),
                    'radiology_modality' => $lastDraft?->radiology_modality ?? null,
                    'radiology_kesan'    => $lastDraft?->radiology_kesan ?? null,
                    'radiology_doctor'   => $lastDraft?->radiology_doctor ?? null,
                    'radiology_image'    => $lastDraft?->radiology_image ?? null,
                    'date'               => $todayIso,
                    'status'             => 'verified',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }

            ClinicalData::create($insertPayload);

            $safeUserId = $this->getSafeUserId();
            DB::table('audit_logs')->insert([
                'user_id'     => $safeUserId,
                'action'      => $isRadiologPath ? 'RADIOLOGY_PACS_UPLOAD' : 'DOCTOR_VERIFY',
                'description' => ($isRadiologPath ? 'Radiolog memverifikasi citra PACS' : 'Dokter memvalidasi rekam medis') . " untuk No. RM: {$norm}",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('audit_logs')->insert([
                'user_id'     => $safeUserId,
                'action'      => 'TIMELINE_SYNCHRONIZATION',
                'description' => "Sinkronisasi lini masa selesai untuk No. RM: {$norm}.",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan ke PostgreSQL rs_uns_db.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("verify() error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal simpan DB: ' . $e->getMessage()], 500);
        }
    }
}