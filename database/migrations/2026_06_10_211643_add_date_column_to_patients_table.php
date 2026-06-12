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
        // 🚀 HOTFIX SECURED: Cek dhisik, nek kolom 'date' durung ono ing Supabase, nembe digawe ben ora duplicate error
        if (!Schema::hasColumn('patients', 'date')) {
            Schema::table('patients', function (Blueprint $table) {
                // Menyisipkan kolom date riil bertipe DATE tepat setelah kolom status_treatment
                $table->date('date')->nullable()->after('status_treatment');
            });
        }
    }

    /**
     * Batalkan penambahan kolom date jika dilakukan rollback migration.
     */
    public function down(): void
    {
        // Pengaman tambahan nalika rollback dijalankan
        if (Schema::hasColumn('patients', 'date')) {
            Schema::table('patients', function (Blueprint $table) {
                // Menghapus kolom date saat proses rollback berjalan
                $table->dropColumn('date');
            });
        }
    }
};