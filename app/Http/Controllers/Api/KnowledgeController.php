<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KnowledgeController extends Controller
{
    public function index() {
        return response()->json(KnowledgeBase::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request) {
        // Validasi input
        $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'file' => 'required|file|mimes:pdf,docx,txt|max:10240', // Max 10MB
        ]);

        try {
            if ($request->hasFile('file')) {
                // Simpan file ke folder storage/app/public/knowledge_files
                $path = $request->file('file')->store('knowledge_files', 'public');
                
                $kb = KnowledgeBase::create([
                    'title' => $request->title,
                    'category' => $request->category,
                    'version' => $request->version ?? '1.0',
                    'description' => $request->description ?? '-',
                    'file_path' => $path,
                    'status' => 'ready' 
                ]);

                return response()->json(['success' => true, 'data' => $kb], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            $kb = KnowledgeBase::findOrFail($id);
            // Hapus file fisik
            Storage::disk('public')->delete($kb->file_path);
            // Hapus record database
            $kb->delete();
            return response()->json(['success' => true, 'message' => 'Dokumen dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}