<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PemeriksaanAwal;
use Illuminate\Http\Request;

class PemeriksaanAwalController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Data yang dikirim dari React
        $request->validate([
            'patient_id' => 'required|string',
            'tensi' => 'required|string',
            'nadi' => 'required|string',
            'suhu' => 'required|string',
            'spo2' => 'required|string',
            'keluhan_awal' => 'nullable|string',
        ]);

        try {
            // 2. Simpan ke Database (Create)
            $pemeriksaan = PemeriksaanAwal::create([
                'patient_id' => $request->patient_id,
                'tensi' => $request->tensi,
                'nadi' => $request->nadi,
                'suhu' => $request->suhu,
                'spo2' => $request->spo2,
                'keluhan_awal' => $request->keluhan_awal,
                'source' => $request->source ?? 'asisten_dokter',
            ]);

            // 3. Kembalikan Response Sukses ke Frontend
            return response()->json([
                'success' => true,
                'message' => 'Data pemeriksaan awal berhasil disimpan.',
                'data' => $pemeriksaan
            ], 201);

        } catch (\Exception $e) {
            // Jika Error
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
}