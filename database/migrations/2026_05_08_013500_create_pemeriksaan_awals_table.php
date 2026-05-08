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
    Schema::create('pemeriksaan_awals', function (Blueprint $table) {
        $table->id();
        $table->string('patient_id'); // Menyimpan No RM
        $table->string('tensi')->nullable();
        $table->string('nadi')->nullable();
        $table->string('suhu')->nullable();
        $table->string('spo2')->nullable();
        $table->text('keluhan_awal')->nullable();
        $table->string('source')->default('asisten_dokter');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_awals');
    }
};
