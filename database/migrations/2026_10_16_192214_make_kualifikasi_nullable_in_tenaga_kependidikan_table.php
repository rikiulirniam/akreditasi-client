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
            $table->string('kualifikasi')->nullable()->default('-')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenaga_kependidikan', function (Blueprint $table) {
            $table->string('kualifikasi')->nullable(false)->change();
        });
    }
};
