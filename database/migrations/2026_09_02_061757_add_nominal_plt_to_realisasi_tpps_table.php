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
        Schema::table('realisasi_tpps', function (Blueprint $table) {
            $table->bigInteger('nominal_plt')->default(0)->after('tpp_bruto');
            $table->json('raw_data')->nullable()->after('total_dibayarkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_tpps', function (Blueprint $table) {
            $table->dropColumn(['nominal_plt', 'raw_data']);
        });
    }
};
