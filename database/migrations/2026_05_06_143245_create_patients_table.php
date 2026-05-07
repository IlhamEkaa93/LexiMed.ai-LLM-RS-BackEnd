<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('patients', function (Blueprint $table) {
            $table->string('no_rm')->primary(); // Primary Key String
            $table->string('name');
            $table->integer('age');
            $table->string('gender'); // L atau P
            $table->string('unit'); // Ruang/Bangsal
            $table->string('dpjp'); // Dokter Penanggung Jawab
            $table->string('status_treatment'); // Rawat Inap / Jalan
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('patients');
    }
};