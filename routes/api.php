<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ClinicalDataController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\UserController;
use App\Models\ClinicalData;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes — LEXIMED.AI PRIVILEGED CORE PROTOCOL
|--------------------------------------------------------------------------
|
| Base Core routing engine terintegrasi Supabase Cloud DB.
| v6.0 - MASTER PRODUCTION FIXED (MENANGGULANGI FATAL EXCEPTION COLUMN ID & DATE)
|
*/

Route::get('/unauthorized', function () {
    return response()->json(['success' => false, 'message' => 'Sesi Berakhir.'], 401);
})->name('login');

// ── AUTENTIKASI UTAMA TOKEN GENERATOR ──
Route::post('/token', function (Request $request) {
    $request->validate(['username' => 'required', 'password' => 'required']);

    if (Auth::attempt($request->only('username', 'password'))) {
        $user = Auth::user();
        $user->tokens()->delete();

        DB::table('audit_logs')->insert([
            'user_id'     => $user->id,
            'action'      => 'LOGIN',
            'description' => "User {$user->name} ({$user->role}) berhasil login.",
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success'      => true,
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'user'         => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'role'     => $user->role ?? 'perawat',
            ],
        ], 200);
    }

    return response()->json(['success' => false, 'message' => 'Kredensial tidak valid.'], 401);
});

// ── PROTECTED ROUTE GATEWAY (SANCTUM) ──
Route::middleware('auth:sanctum')->group(function () {

    // ── AUDIT LOGS SECURITY ENGINE ──
    Route::get('/audit-logs', function () {
        try {
            $logs = DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select('audit_logs.*', 'users.name as real_name')
                ->orderBy('audit_logs.created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(fn($log) => [
                    'id'     => $log->id,
                    'time'   => $log->created_at ? date('Y-m-d H:i:s', strtotime($log->created_at)) : now()->toDateTimeString(),
                    'user'   => $log->real_name ?? 'System / Cloud AI',
                    'action' => $log->action,
                    'target' => $log->description ?? '-',
                    'status' => 'Success',
                ]);

            return response()->json([
                'success' => true,
                'logs'    => $logs,
                'stats'   => [
                    'total'  => DB::table('audit_logs')->count(),
                    'alerts' => DB::table('audit_logs')->where('action', 'LIKE', '%ALERT%')->count(),
                    'time'   => '1.1s',
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    // ── DASHBOARD STATS ANALYTICS ──
    Route::get('/dashboard-stats', function () {
        try {
            $todayDate = date('Y-m-d');
            return response()->json([
                'success'           => true,
                'total_staff'       => DB::table('users')->count(),
                'total_logs'        => DB::table('audit_logs')->count(),
                'total_documents'   => DB::table('knowledge_bases')->count(),
                'system_uptime'     => '99.9%',
                'today_patients'    => DB::table('patients')->whereDate('created_at', $todayDate)->count(), 
                'pending_ai'        => ClinicalData::where('status', 'draft')->count(),
                'completed_resumes' => ClinicalData::where('status', 'verified')->count(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    // ── PATIENTS INTERACTIVE LIVE QUEUE NODE ──
    Route::get('/patients-list', function () {
        try {
            $todayIso = date('Y-m-d');

            // FIX FIX: Mengubah pengurutan dari 'id' menjadi 'created_at' agar sinkron dengan skema Supabase
            $patients = DB::table('patients')
                ->whereDate('created_at', $todayIso)
                ->orderBy('created_at', 'desc')
                ->get();

            $mappedPatients = $patients->map(function($p) use ($todayIso) {
                $pData = (array) $p;
                $pDpjp = isset($pData['dpjp']) ? (string)$pData['dpjp'] : '';

                $cleanDpjp = strtolower(str_replace(['.', ' '], '', $pDpjp));
                if (strpos($cleanDpjp, 'tirta') !== false || empty($pDpjp)) {
                    $pData['dpjp'] = 'Dr. Tirta';
                }
                
                // Suntik properti virtual 'date' agar filter frontend aman
                $pData['date'] = $todayIso;
                return $pData;
            });

            return response()->json($mappedPatients->values()->all(), 200);
        } catch (\Throwable $e) {
            Log::error("Error Severe Patients-List Server Side: " . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat basis data harian: ' . $e->getMessage()], 500);
        }
    });

    Route::get('/patients/{query}',    [PatientController::class, 'show']);
    Route::post('/patients',           [PatientController::class, 'store']);

    Route::get('/patients/{rm}/history', function ($rm) {
        try {
            $history = ClinicalData::where('patient_id', $rm)
                ->where('status', 'verified')
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($history, 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });

    // ── CLINICAL CORE DATA ARS_UNS ENGINE ──
    Route::get('/clinical-data', function () {
        try {
            $data = ClinicalData::orderBy('created_at', 'desc')->get();
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    Route::post('/clinical-data', function (Request $request) {
        DB::beginTransaction();
        try {
            $rawContent = $request->raw_content;
            if (is_array($rawContent)) {
                $rawContent = json_encode($rawContent);
            }

            $validSources = ['manual', 'whatsapp', 'voice', 'radiologi'];
            $finalSource  = in_array($request->source ?? 'manual', $validSources) ? ($request->source ?? 'manual') : 'manual';

            $data = ClinicalData::create([
                'patient_id'        => $request->patient_id,
                'raw_content'       => $rawContent,
                'blood_pressure'    => $request->blood_pressure ?? null,
                'heart_rate'        => $request->heart_rate ?? null,
                'temperature'       => $request->temperature ?? null,
                'oxygen_saturation' => $request->oxygen_saturation ?? null,
                'status'            => $request->status ?? 'draft',
                'source'            => $finalSource,
            ]);

            if (Auth::check()) {
                $user = Auth::user();
                DB::table('audit_logs')->insert([
                    'user_id'     => $user->id,
                    'action'      => 'DATA_INPUT',
                    'description' => "Staf {$user->name} menyimpan data medis pasien RM: {$request->patient_id}",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $data], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    Route::get('/clinical-data/{norm}',                  [ClinicalDataController::class, 'show']);
    Route::post('/clinical-data/{norm}/generate-ai',     [ClinicalDataController::class, 'generateAI']);
    Route::post('/clinical-data/{norm}/radiology-order', [ClinicalDataController::class, 'storeRadiologyOrder']);
    Route::patch('/clinical-data/{norm}/verify',         [ClinicalDataController::class, 'verify']);
});

Route::post('/agent-sandbox', [ClinicalDataController::class, 'sandboxExecute']);