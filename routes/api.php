<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use App\Http\Controllers\Api\ClinicalDataController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Models\ClinicalData;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes - DARSI RS UNS (Full Intelligence System)
|--------------------------------------------------------------------------
*/

// --- 0. FALLBACK UNAUTHORIZED ---
Route::get('/unauthorized', function () {
    return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Token tidak valid atau sesi Anda telah berakhir.'
    ], 401);
})->name('login');


// --- 1. AUTHENTICATION (Public) ---
Route::post('/token', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $credentials = $request->only('username', 'password');
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $user->tokens()->delete(); 
        
        return response()->json([
            'success'      => true,
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'user'         => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'role'     => $user->role ?? 'perawat'
            ]
        ], 200);
    }
    
    return response()->json([
        'success' => false, 
        'message' => 'Kredensial tidak valid.'
    ], 401);
});


// --- 2. PROTECTED ROUTES (Requires Bearer Token) ---
Route::middleware('auth:sanctum')->group(function () {

    /**
     * REALTIME AUDIT LOGS
     */
    Route::get('/audit-logs', function() {
        try {
            $logs = DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id') 
                ->select('audit_logs.*', 'users.name as real_name')
                ->orderBy('audit_logs.created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($log) {
                    return [
                        'id'     => $log->id,
                        'time'   => $log->created_at ? date('Y-m-d H:i:s', strtotime($log->created_at)) : now()->toDateTimeString(),
                        'user'   => $log->real_name ?? 'System / Cloud AI',
                        'action' => $log->action,
                        'target' => $log->description ?? '-', 
                        'status' => 'Success'
                    ];
                });

            return response()->json([
                'success' => true,
                'logs'    => $logs,
                'stats'   => [
                    'total'  => DB::table('audit_logs')->count(),
                    'alerts' => DB::table('audit_logs')->where('action', 'LIKE', '%ALERT%')->count(),
                    'time'   => '1.1s' 
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error("Audit Log Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    /**
     * STATISTIK DASHBOARD (Ini yang dicari oleh Frontend!)
     */
    Route::get('/dashboard-stats', function() {
        try {
            return response()->json([
                'success' => true,
                'total_staff'       => DB::table('users')->count(),
                'total_logs'        => DB::table('clinical_data')->count(), 
                'total_documents'   => DB::table('knowledge_bases')->count(),
                'system_uptime'     => '99.9%',
                'today_patients'    => DB::table('patients')->whereDate('created_at', date('Y-m-d'))->count(),
                'pending_ai'        => ClinicalData::where('status', 'draft')->count(),
                'completed_resumes' => ClinicalData::where('status', 'verified')->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    // ... (rute lainnya tetap aman di bawah ini) ...
    Route::get('/radiology/dashboard', function() {
        return response()->json(['stats' => [], 'recent_work' => []], 200);
    });

    Route::get('/clinical-data', function() {
        return response()->json(['success' => true, 'data' => ClinicalData::orderBy('created_at', 'desc')->get()], 200);
    });

    Route::get('/manajemen/dashboard', function() {
        return response()->json(['stats' => [], 'reports' => []], 200);
    });
});
