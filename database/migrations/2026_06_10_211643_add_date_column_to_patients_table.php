<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan penambahan kolom date asli ke dalam tabel patients.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Menyisipkan kolom date riil bertipe DATE tepat setelah kolom status_treatment
            $table->date('date')->nullable()->after('status_treatment');
        });
    }

    /**
     * Batalkan penambahan kolom date jika dilakukan rollback migration.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Menghapus kolom date saat proses rollback berjalan
            $table->dropColumn('date');
        });
    }
};