<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient; // Pastikan Model Patient sudah dibuat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * Menampilkan semua daftar pasien.
     * Digunakan oleh Admin untuk melihat list pasien di PostgreSQL.
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
     * Menyimpan data pasien baru atau update jika NORM sudah ada.
     * Mendukung kolom title (Tn, Ny, An, Nona).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input sesuai skema database terbaru
        $validated = $request->validate([
            'no_rm'            => 'required|string',
            'title'            => 'nullable|string', // Contoh: Tn, Ny, An
            'name'             => 'required|string',
            'age'              => 'required|integer',
            'gender'           => 'required|string', // Laki-Laki atau Perempuan
            'unit'             => 'required|string',
            'dpjp'             => 'required|string',
            'status_treatment' => 'required|string',
        ]);

        try {
            /**
             * 2. Simpan menggunakan updateOrCreate.
             * Jika no_rm sudah ada di PostgreSQL, data akan diperbarui.
             * Jika belum ada, data akan dibuat baru (Insert).
             */
            $patient = Patient::updateOrCreate(
                ['no_rm' => $validated['no_rm']], 
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Pasien ' . $patient->name . ' berhasil terdaftar di sistem.',
                'data'    => $patient
            ], 201);

        } catch (\Exception $e) {
            Log::error("Gagal registrasi pasien: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ke PostgreSQL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu pasien berdasarkan NORM.
     * Fungsi ini dipanggil oleh Dokter dan Asisten.
     */
    public function show($rm)
    {
        // Cari berdasarkan Primary Key (no_rm)
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