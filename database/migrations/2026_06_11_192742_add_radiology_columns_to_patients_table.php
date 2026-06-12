<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🚀 HOTFIX SECURED: Cek per kolom sacara individual, nek durung ono ing Supabase nembe digawe
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'radiology_modality')) {
                $table->string('radiology_modality')->nullable();
            }
            
            // Jaga-jaga nek ono kolom hasil/status radiologi liyane ing berkas aslimu, proteksi sisan kene
            if (!Schema::hasColumn('patients', 'radiology_result')) {
                $table->text('radiology_result')->nullable();
            }
            
            if (!Schema::hasColumn('patients', 'radiology_status')) {
                $table->string('radiology_status')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'radiology_modality')) {
                $table->dropColumn('radiology_modality');
            }
            if (Schema::hasColumn('patients', 'radiology_result')) {
                $table->dropColumn('radiology_result');
            }
            if (Schema::hasColumn('patients', 'radiology_status')) {
                $table->dropColumn('radiology_status');
            }
        });
    }
};