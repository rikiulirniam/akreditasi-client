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
        Schema::create('sertifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenaga_kependidikan_id')->constrained('tenaga_kependidikan')->cascadeOnDelete();
            $table->string('nama_sertifikasi');
            $table->string('penerbit');
            $table->string('nomor_sertifikasi')->nullable();
            $table->string('tahun')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikasis');
    }
};
