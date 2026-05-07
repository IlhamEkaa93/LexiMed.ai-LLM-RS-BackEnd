<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('radiology_reports', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id'); // NORM
            $table->string('modality'); // X-Ray, CT-Scan, MRI, USG
            $table->text('raw_findings'); // Catatan mentah dari radiografer/radiolog
            $table->text('ai_result')->nullable(); // Hasil draft/ringkasan AI
            $table->string('status')->default('draft'); // draft, verified
            $table->string('radiologist')->nullable(); // Nama dokter yang memvalidasi
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('radiology_reports');
    }
};