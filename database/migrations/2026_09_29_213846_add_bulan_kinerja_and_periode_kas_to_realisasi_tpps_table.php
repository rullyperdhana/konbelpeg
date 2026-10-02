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
            $table->string('periode_kas')->nullable()->index()->after('periode');
            $table->string('bulan_kinerja')->nullable()->index()->after('periode_kas');
            $table->string('tahap_bayar')->nullable()->after('bulan_kinerja');
            $table->text('keterangan_bayar')->nullable()->after('tahap_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_tpps', function (Blueprint $table) {
            $table->dropColumn(['periode_kas', 'bulan_kinerja', 'tahap_bayar', 'keterangan_bayar']);
        });
    }
};
