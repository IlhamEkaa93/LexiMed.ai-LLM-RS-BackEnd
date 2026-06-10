<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * Menampilkan semua daftar pasien.
     */
    public function index()
    {
        try {
            $patients = Patient::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data'    => $patients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pasien: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data pasien baru atau update kunjungan berobat ulang.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input - Kolom date dimasukkan secara ketat ke sirkuit pengaman Laravel
        $validated = $request->validate([
            'no_rm'            => 'required|string',
            'title'            => 'nullable|string', 
            'name'             => 'required|string',
            'age'              => 'required|integer',
            'gender'           => 'required|string', 
            'unit'             => 'required|string',
            'dpjp'             => 'required|string',
            'status_treatment' => 'required|string',
            'date'             => 'nullable|string', // 🚀 FIX: Diizinkan masuk mass assignment
        ]);

        try {
            // 2. Eksekusi update atau create berdasarkan kecocokan nomor rekam medis
            $patient = Patient::updateOrCreate(
                ['no_rm' => $validated['no_rm']], 
                $validated
            );

            // 🚀 FIX MUTLAK JURI PREPARATION: 
            // Jika pasien melakukan "Berobat Ulang", paksa baris updated_at mencatat waktu detik ini
            // agar antrean di dasbor dokter otomatis melesat naik ke posisi paling atas!
            $patient->touch(); 

            return response()->json([
                'success' => true,
                'message' => 'Pasien ' . $patient->name . ' berhasil terdaftar di sistem.',
                'data'    => $patient
            ], 201);

        } catch (\Exception $e) {
            Log::error("Gagal registrasi pasien master: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ke PostgreSQL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu pasien berdasarkan NORM.
     */
    public function show($rm)
    {
        $patient = Patient::where('no_rm', $rm)->first();
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien dengan nomor ' . $rm . ' tidak ditemukan di database.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $patient
        ], 200);
    }

    /**
     * Menghapus data pasien (Opsional untuk Admin).
     */
    public function destroy($rm)
    {
        $patient = Patient::where('no_rm', $rm)->first();
        
        if ($patient) {
            $patient->delete();
            return response()->json(['success' => true, 'message' => 'Data pasien dihapus.']);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
    }
}