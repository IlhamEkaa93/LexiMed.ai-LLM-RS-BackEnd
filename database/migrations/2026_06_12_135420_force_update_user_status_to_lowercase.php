<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🚀 MASTER FIX: Maksa kabeh kolom status dadi 'aktif' lowercase langsung nang tabel database
        if (Schema::hasTable('users')) {
            DB::table('users')->update(['status' => 'aktif']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};