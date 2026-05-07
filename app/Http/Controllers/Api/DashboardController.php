<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\ClinicalData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Mendapatkan user yang sedang login untuk menyesuaikan output role
        $user = $request->user();

        // Data Dasar Database
        $countStaff = User::count();
        $countPatients = ClinicalData::distinct('patient_id')->count();
        $countLogs = ClinicalData::count();

        // Response default untuk Admin
        $response = [
            'success' => true,
            'total_staff' => $countStaff,
            'total_patients' => $countPatients,
            'total_logs' => $countLogs,
            'system_uptime' => '99.9%',
        ];

        // Tambahan data spesifik untuk Dashboard Dokter/Perawat (Frontend compatibility)
        $response['today_patients'] = (string) $countPatients;
        $response['pending_ai'] = (string) ClinicalData::where('status', 'draft')->count();
        $response['completed_resumes'] = (string) ClinicalData::where('status', 'verified')->count();

        return response()->json($response);
    }
}