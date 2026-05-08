<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan Migrasi untuk tabel Patients (PostgreSQL)
     */
    public function up(): void {
        Schema::create('patients', function (Blueprint $table) {
            // No Rekam Medis sebagai Primary Key
            $table->string('no_rm')->primary(); 
            
            // Kolom Gelar/Panggilan (An, Ny, Tn, Nona)
            $table->string('title')->nullable(); 
            
            // Nama Lengkap
            $table->string('name');
            
            // Umur (Integer)
            $table->integer('age')->nullable();
            
            // Jenis Kelamin (Dibuat string panjang agar bisa menampung "Laki-Laki" / "Perempuan")
            $table->string('gender')->nullable(); 
            
            // Unit / Poli / Bangsal
            $table->string('unit')->nullable(); 
            
            // Dokter Penanggung Jawab
            $table->string('dpjp')->nullable(); 
            
            // Status Perawatan (Rawat Inap, Jalan, IGD)
            $table->string('status_treatment')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Batalkan Migrasi
     */
    public function down(): void {
        Schema::dropIfExists('patients');
    }
};