<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    /**
     * Menampilkan daftar semua pasien (Opsional, berguna untuk dashboard)
     */
    public function index()
    {
        $patients = DB::table('patients')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $patients
        ], 200);
    }

    /**
     * Menyimpan data pasien baru
     */
    public function store(Request $request)
    {
        // Validasi data dari React
        $validated = $request->validate([
            'no_rm' => 'required|string',
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'unit' => 'required|string',
            'dpjp' => 'required|string',
            'status_treatment' => 'required|string',
        ]);

        // Simpan ke database PostgreSQL menggunakan Query Builder
        DB::table('patients')->insert([
            'no_rm' => $validated['no_rm'],
            'name' => $validated['name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'unit' => $validated['unit'],
            'dpjp' => $validated['dpjp'],
            'status_treatment' => $validated['status_treatment'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil didaftarkan.',
            'name' => $validated['name']
        ], 201);
    }

    /**
     * Menampilkan detail satu pasien berdasarkan Nomor Rekam Medis (no_rm)
     * INI ADALAH FUNGSI YANG DIPANGGIL OLEH FRONTEND SAAT PENCARIAN
     */
    public function show($rm)
    {
        // Cari pasien berdasarkan no_rm
        $patient = DB::table('patients')->where('no_rm', $rm)->first();
        
        // Jika pasien tidak ditemukan, kembalikan error 404
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien dengan nomor ' . $rm . ' tidak ditemukan.'
            ], 404);
        }

        // Jika berhasil, kembalikan data pasien. 
        // Kita langsung mengembalikan objeknya tanpa perlu memanggil $patient->id di backend
        return response()->json([
            'success' => true,
            'data' => $patient
        ], 200);
    }
}