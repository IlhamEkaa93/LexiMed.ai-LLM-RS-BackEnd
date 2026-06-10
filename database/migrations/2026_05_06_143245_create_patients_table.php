<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan Migrasi untuk membuat struktur dasar tabel Patients (Supabase PostgreSQL).
     */
    public function up(): void {
        Schema::create('patients', function (Blueprint $table) {
            // Nomor Rekam Medis bertindak sebagai string Primary Key (Kunci Utama)
            $table->string('no_rm')->primary(); 
            
            // Kolom Panggilan Kehormatan Pasien (An, Ny, Tn, Nona, dll.)
            $table->string('title')->nullable(); 
            
            // Nama Lengkap Pasien sesuai identitas KTP
            $table->string('name');
            
            // Umur Pasien (Format Data Biner Integer)
            $table->integer('age')->nullable();
            
            // Jenis Kelamin Pasien (Menampung string "Laki-Laki" / "Perempuan")
            $table->string('gender')->nullable(); 
            
            // Penempatan Unit / Poliklinik Pelayanan / Ruang Bangsal
            $table->string('unit')->nullable(); 
            
            // Nama Dokter Penanggung Jawab Pelayanan (DPJP)
            $table->string('dpjp')->nullable(); 
            
            // Status Perawatan (Rawat Inap, Rawat Jalan, atau Triage IGD)
            $table->string('status_treatment')->nullable(); 
            
            // Otomatis membuat kolom created_at dan updated_at dari Laravel Engine
            $table->timestamps();
        });
    }

    /**
     * Batalkan seluruh struktur tabel jika melakukan proses rollback migration.
     */
    public function down(): void {
        Schema::dropIfExists('patients');
    }
};