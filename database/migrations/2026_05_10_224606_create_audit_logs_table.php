<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel audit_logs.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); // Primary Key
            
            // Relasi ke tabel users (Siapa yang melakukan aksi)
            // Dibuat nullable() jaga-jaga kalau ada aksi dari System/AI yang tidak punya user_id
            $table->unsignedBigInteger('user_id')->nullable(); 
            
            // Nama Aksi (Contoh: DATA_INPUT, LOGIN, AI SUMMARIZATION)
            $table->string('action');
            
            // Deskripsi detail (Contoh: "Staf dr. Tirta menyimpan data medis pasien RM-1")
            $table->text('description')->nullable();
            
            // Menyimpan created_at dan updated_at
            $table->timestamps();
            
            // Opsional: Menambahkan foreign key agar data terhubung dengan tabel users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Batalkan migrasi (Hapus tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};