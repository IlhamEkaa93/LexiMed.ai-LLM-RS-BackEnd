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
        Schema::table('patients', function (Blueprint $table) {
            // Menambahkan kolom penunjang radiologi secara aman ke Supabase
            $table->string('radiology_modality')->nullable()->after('status_treatment');
            $table->text('radiology_image')->nullable()->after('radiology_modality');
            $table->text('radiology_kesan')->nullable()->after('radiology_image');
            $table->string('radiology_doctor')->nullable()->after('radiology_kesan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Rollback guardrail jika migration dibatalkan
            $table->dropColumn(['radiology_modality', 'radiology_image', 'radiology_kesan', 'radiology_doctor']);
        });
    }
};