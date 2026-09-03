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
        Schema::create('unmatched_nips', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->index();
            $table->string('nama')->nullable();
            $table->string('jenis_file'); // 'Gaji' atau 'TPP'
            $table->string('periode');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unmatched_nips');
    }
};
