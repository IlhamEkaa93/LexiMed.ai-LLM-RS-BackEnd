<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations — SOLIDIFIED UNIFIED CORE KNOWLEDGE BASE SCHEMA
     */
    public function up(): void
    {
        // Jalankan proteksi guardrail pembentukan tabel penampung SOP RAG
        if (!Schema::hasTable('knowledge_bases')) {
            Schema::create('knowledge_bases', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category'); // Misal: SOP Pelayanan, Guideline Medis, Farmakope
                $table->string('file_path');
                $table->string('version')->default('1.0');
                $table->text('description')->nullable();
                $table->string('status')->default('ready'); // processing, ready, error
                $table->time('created_time')->nullable(); // 🚀 SUNTIK MUTLAK: Menampung jam menit detik riil upload SOP
                $table->timestamps();
            });
        } else {
            // Jalankan failover jika tabel sudah terbentuk di Supabase namun kolom jam tertinggal
            Schema::table('knowledge_bases', function (Blueprint $table) {
                if (!Schema::hasColumn('knowledge_bases', 'created_time')) {
                    $table->time('created_time')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};