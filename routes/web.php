<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Kita pasang rute di web.php agar Vercel pasti menemukannya
Route::get('/api/dashboard-stats', function() {
    return response()->json([
        'success' => true,
        'message' => 'Rute terdeteksi via Web.php',
        'total_staff' => DB::table('users')->count(),
        'total_logs' => DB::table('audit_logs')->count(),
        'total_documents' => DB::table('knowledge_bases')->count(),
        'system_uptime' => '99.9%'
    ], 200);
});

Route::get('/', function () {
    return view('welcome');
});
