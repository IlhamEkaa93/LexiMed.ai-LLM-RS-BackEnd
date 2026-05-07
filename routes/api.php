<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ClinicalDataController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PatientController;
use App\Models\ClinicalData;

/*
|--------------------------------------------------------------------------
| API Routes - DARSI RS UNS
|--------------------------------------------------------------------------
*/

// --- 1. AUTHENTICATION (Public) ---
Route::post('/token', function (Request $request) {
    $credentials = $request->only('username', 'password');
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        // Hapus token lama agar satu user hanya punya satu session aktif
        $user->tokens()->delete();
        
        return response()->json([
            'success' => true,
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role ?? 'perawat'
            ]
        ]);
    }
    
    return response()->json([
        'success' => false, 
        'message' => 'Kredensial tidak valid. Silakan cek ID dan Password Anda.'
    ], 401);
});

// --- 2. PROTECTED ROUTES (Auth Required) ---
Route::middleware('auth:sanctum')->group(function () {

    /**
     * STATS DASHBOARD UTAMA
     */
    Route::get('/dashboard-stats', function() {
        return response()->json([
            'today_patients'    => DB::table('patients')->count(),
            'pending_ai'        => ClinicalData::where('status', 'draft')->count(),
            'completed_resumes' => ClinicalData::where('status', 'verified')->count()
        ]);
    });

    /**
     * MANAJEMEN PASIEN
     */
    // Registrasi pasien baru menggunakan Controller
    Route::post('/patients', [PatientController::class, 'store']);
    
    // List Seluruh Pasien
    Route::get('/patients-list', function() {
        try {
            $data = DB::table('patients')->orderBy('created_at', 'desc')->get();
            return response()->json($data->map(function($p) {
                return [
                    // PERBAIKAN: Gunakan ?? untuk mencegah error jika kolom id tidak ada
                    'id'      => $p->id ?? $p->id_pasien ?? 0,
                    'name'    => $p->name ?? 'Tanpa Nama', 
                    'norm'    => $p->no_rm ?? $p->patient_id ?? '-',
                    'status'  => $p->status_treatment ?? 'Rawat Jalan',
                    'date'    => isset($p->created_at) ? date('d/m/Y', strtotime($p->created_at)) : date('d/m/Y')
                ];
            }));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error mengambil list: ' . $e->getMessage()], 500);
        }
    });

    // Detail Pasien berdasarkan Nomor RM (Untuk fitur Pencarian Pasien)
    Route::get('/patients/{rm}', function($rm) {
        try {
            // Pencarian data
            $patient = DB::table('patients')
                        ->where('no_rm', $rm)
                        ->orWhere('no_rm', 'RM-'.$rm)
                        ->first();

            if ($patient) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'norm'    => $patient->no_rm,
                        'name'    => $patient->name,
                        'status'  => $patient->status_treatment,
                        'room'    => $patient->unit ?? 'UMUM',
                        'gender'  => ($patient->gender === 'L' || $patient->gender === 'Laki-Laki') ? 'Laki-Laki' : 'Perempuan',
                        'age'     => $patient->age,
                        // PERBAIKAN BUG UTAMA: Mencegah error Undefined property: stdClass::$id
                        'id'      => $patient->id ?? $patient->id_pasien ?? $patient->no_rm
                    ]
                ]);
            }
            
            return response()->json(['success' => false, 'message' => "Pasien dengan nomor $rm tidak ditemukan."], 404);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    /**
     * HISTORI REKAM MEDIS
     */
    Route::get('/patients/{rm}/history', function($rm) {
        return ClinicalData::where('patient_id', $rm)
                    ->where('status', 'verified')
                    ->orderBy('created_at', 'desc')
                    ->get();
    });

    /**
     * CLINICAL DATA & AI ENGINE
     */
    Route::post('/clinical-data', [ClinicalDataController::class, 'store']);
    
    Route::get('/clinical-data/{norm}', function($norm) {
        $data = ClinicalData::where('patient_id', $norm)->latest()->first();
        return $data ? response()->json($data) : response()->json(['message' => 'Data klinis belum ada'], 404);
    });

    /**
     * VERIFIKASI DOKTER (Human-in-the-loop)
     */
    Route::patch('/clinical-data/{norm}/verify', function($norm, Request $request) {
        $data = ClinicalData::where('patient_id', $norm)->latest()->first();
        
        if ($data) {
            $data->update([
                'status' => 'verified', 
                'ai_summary' => $request->input('final_summary', $data->ai_summary)
            ]);
            return response()->json(['success' => true, 'message' => 'Dokumen klinis tervalidasi.']);
        }
        return response()->json(['success' => false, 'message' => 'Draf tidak ditemukan.'], 404);
    });

    /**
     * MODUL MANAJEMEN / EKSEKUTIF
     */
    Route::get('/manajemen/dashboard', function() {
        return response()->json([
            'stats' => [
                'totalPasien'  => DB::table('patients')->count(),
                'avgTunggu'    => '45m',
                'utilBed'      => '82%',
                'totalLayanan' => 3420
            ],
            'reports' => [
                ['id' => 1, 'date' => date('Y-m-d'), 'title' => 'Laporan Performa Unit Gawat Darurat', 'status' => 'Final'],
                ['id' => 2, 'date' => date('Y-m-d'), 'title' => 'Analisis Waktu Tunggu Layanan', 'status' => 'Final']
            ]
        ]);
    });

    Route::post('/manajemen/reports', function(Request $request) {
        return response()->json(['success' => true, 'message' => 'Ringkasan eksekutif berhasil disimpan.']);
    });
});