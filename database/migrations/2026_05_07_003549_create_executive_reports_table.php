<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executive_reports', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->text('summary_content');
            $table->string('status')->default('final');
            $table->string('created_by')->nullable(); // ID/Nama Direktur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executive_reports');
    }
};