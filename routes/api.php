<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/* TEST RUTE LANGSUNG */
Route::get('/dashboard-stats', function() {
    return response()->json([
        'success' => true,
        'message' => 'RUTE TERDETEKSI',
        'data_asli' => [
            'total_staff' => DB::table('users')->count(),
            'total_logs' => DB::table('audit_logs')->count(),
            'total_documents' => DB::table('knowledge_bases')->count(),
            'system_uptime' => '99.9%'
        ]
    ]);
});

Route::post('/token', function (Request $request) {
    return response()->json(['status' => 'Login route active']);
});
