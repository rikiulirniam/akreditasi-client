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
        Schema::table('tenaga_kependidikan', function (Blueprint $table) {
            $table->string('bidang_keahlian')->nullable()->after('kualifikasi');
            $table->string('program_studi')->nullable()->after('bidang_keahlian');
            $table->string('email')->nullable()->after('program_studi');
            $table->string('status')->default('Aktif')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenaga_kependidikan', function (Blueprint $table) {
            $table->dropColumn(['bidang_keahlian', 'program_studi', 'email', 'status']);
        });
    }
};
