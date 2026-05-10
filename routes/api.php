<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/* Public Route */
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
    return response()->json(['success' => false, 'message' => 'Login Gagal'], 401);
});

/* Protected Routes */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard-stats', function() {
        return response()->json([
            'success' => true,
            'total_staff' => DB::table('users')->count(),
            'total_logs' => DB::table('audit_logs')->count(),
            'total_documents' => DB::table('knowledge_bases')->count(),
            'system_uptime' => '99.9%'
        ]);
    });
});
