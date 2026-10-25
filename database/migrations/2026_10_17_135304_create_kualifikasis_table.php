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
        Schema::create('kualifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenaga_kependidikan_id')->constrained('tenaga_kependidikan')->cascadeOnDelete();
            $table->string('gelar')->nullable(); // e.g. Dr
            $table->string('jenjang')->nullable(); // e.g. S3, S2, S1
            $table->string('institusi')->nullable(); // e.g. Politeknik Negeri Semarang
            $table->string('program_studi')->nullable(); // e.g. Teknik Informatika
            $table->string('tahun_lulus')->nullable(); // e.g. 2026
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kualifikasis');
    }
};
