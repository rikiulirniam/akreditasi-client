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
        Schema::create('tenaga_kependidikan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nip', 50)->unique();
            $table->string('jabatan');
            $table->string('kualifikasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenaga_kependidikan');
    }
};
