<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes - DARSI RS UNS
|--------------------------------------------------------------------------
*/

// RUTE TES (Paling Atas agar prioritas)
Route::get('/dashboard-stats', function() {
    try {
        return response()->json([
            'success' => true,
            'total_staff' => DB::table('users')->count(),
            'total_logs' => DB::table('audit_logs')->count(),
            'total_documents' => DB::table('knowledge_bases')->count(),
            'system_uptime' => '99.9%'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database connection active but table error',
            'error' => $e->getMessage()
        ], 500);
    }
});

// RUTE AUTH (Public)
Route::post('/token', function (Request $request) {
    $credentials = $request->only('username', 'password');
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }
    return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
});

// RUTE PROTECTED (Butuh Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', function(Request $request) {
        return $request->user();
    });
});
