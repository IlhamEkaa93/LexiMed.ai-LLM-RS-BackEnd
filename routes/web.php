<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\ClinicalDataController;

/*
|--------------------------------------------------------------------------
| DARSI RS UNS - Web Routes
|--------------------------------------------------------------------------
|
| File ini menangani rute utama untuk autentikasi sistem dan 
| integrasi data klinis dengan AI Agent.
|
*/

// 1. Rute Halaman Utama (Default Laravel)
Route::get('/', function () {
    return view('welcome');
});

/**
 * 2. Rute Autentikasi Token (OAuth2/Sanctum)
 * Menangani permintaan login dari Frontend React.
 * URL: http://127.0.0.1:8000/token
 */
Route::post('/token', function (Request $request) {
    // Mengambil username dan password dari FormData
    $credentials = $request->only('username', 'password');

    // Validasi login menggunakan kolom 'username' yang baru saja kita migrasikan
    if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
        $user = Auth::user();
        
        // Membuat token akses menggunakan Laravel Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'role' => $user->role
            ]
        ]);
    }

    // Jika kredensial salah atau tidak ada di PostgreSQL
    return response()->json([
        'message' => 'Kredensial tidak valid atau akun tidak ditemukan di database.'
    ], 401);
});

/**
 * 3. Rute Fitur Klinis DARSI
 * Menangani input catatan medis untuk diolah oleh AI (Llama 3.3).
 * URL: http://127.0.0.1:8000/api/clinical-data
 */
Route::prefix('api')->group(function () {
    Route::post('/clinical-data', [ClinicalDataController::class, 'store']);
});