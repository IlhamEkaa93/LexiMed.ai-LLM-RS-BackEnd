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
    Schema::create('knowledge_bases', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('category'); // Misal: SOP, Jurnal, Alur Kerja
        $table->string('file_path');
        $table->string('version')->default('1.0');
        $table->text('description')->nullable();
        $table->string('status')->default('processing'); // processing, ready, error
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
