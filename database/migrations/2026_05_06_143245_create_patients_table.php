<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations — UNIFIED CORE PATIENTS SCHEMA WITH STATUS TREATMENT
     */
    public function up(): void
    {
        if (!Schema::hasTable('patients')) {
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
                
                // 🚀 KOLOM UTAMA PERAWAT: Status Perawatan (Rawat Inap, Rawat Jalan, UGD / Triage IGD)
                $table->string('status_treatment')->default('Rawat Jalan'); 
                
                // Menyisipkan kolom date riil bertipe DATE tepat setelah kolom status_treatment
                $table->date('date')->nullable();

                // Kolom penunjang radiologi PACS terintegrasi lintas node
                $table->string('radiology_modality')->nullable();
                $table->text('radiology_image')->nullable();
                $table->text('radiology_kesan')->nullable();
                $table->string('radiology_doctor')->nullable();
                
                // Otomatis membuat kolom created_at dan updated_at dari Laravel Engine
                $table->timestamps();
            });
        } else {
            // Guardrail: Jika tabel sudah ada, suntik kolom 'date' and 'status_treatment' jika belum eksis
            Schema::table('patients', function (Blueprint $table) {
                if (!Schema::hasColumn('patients', 'status_treatment')) {
                    $table->string('status_treatment')->default('Rawat Jalan')->after('dpjp');
                }
                if (!Schema::hasColumn('patients', 'date')) {
                    $table->date('date')->nullable()->after('status_treatment');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};