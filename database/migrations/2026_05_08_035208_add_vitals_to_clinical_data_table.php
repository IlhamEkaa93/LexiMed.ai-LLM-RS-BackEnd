<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('clinical_data', function (Blueprint $table) {
        $table->string('blood_pressure')->nullable(); // Tensi
        $table->string('heart_rate')->nullable();     // Nadi
        $table->string('temperature')->nullable();    // Suhu
        $table->string('oxygen_saturation')->nullable(); // SpO2
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinical_data', function (Blueprint $table) {
            //
        });
    }
};
