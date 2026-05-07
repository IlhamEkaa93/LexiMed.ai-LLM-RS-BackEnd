<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel clinical_data.
     * Tabel ini digunakan untuk menyimpan catatan medis mentah dan hasil AI.
     */
    public function up(): void
    {
        Schema::create('clinical_data', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('patient_id'); // ID Rekam Medis (RM-xxxx)
            
            // Sumber data: bisa dari ketik manual, chat WA, atau rekaman suara
            $table->enum('source', ['manual', 'whatsapp', 'voice'])->default('manual');
            
            $table->text('raw_content'); // Catatan berantakan dari dokter
            $table->text('ai_summary')->nullable(); // Hasil rapi dari Llama 3.3
            
            $table->string('status')->default('draft'); // Status dokumen (draft/final)
            $table->timestamps(); // create_at & updated_at
        });
    }

    /**
     * Batalkan migrasi (Hapus tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_data');
    }
};