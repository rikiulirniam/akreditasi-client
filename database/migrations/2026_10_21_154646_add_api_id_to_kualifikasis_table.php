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
        Schema::table('kualifikasis', function (Blueprint $table) {
            // API tendik ID — no FK constraint because the record lives in an external API
            $table->unsignedBigInteger('tendik_api_id')->nullable()->after('id');
            // Make local FK nullable so rows without a local tendik record are allowed
            $table->unsignedBigInteger('tenaga_kependidikan_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kualifikasis', function (Blueprint $table) {
            $table->dropColumn('tendik_api_id');
            $table->unsignedBigInteger('tenaga_kependidikan_id')->nullable(false)->change();
        });
    }
};
