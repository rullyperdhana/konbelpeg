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
        Schema::create('realisasi_tpps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            $table->string('periode');
            $table->bigInteger('tpp_bruto')->default(0);
            $table->bigInteger('tpp_netto')->default(0);
            $table->bigInteger('pph_21')->default(0);
            $table->bigInteger('potongan_lainnya')->default(0);
            $table->bigInteger('iuran_iwp')->default(0);
            $table->bigInteger('total_dibayarkan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_tpps');
    }
};
