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
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->after('nama')->index();
            $table->string('no_rekening', 50)->nullable()->after('nik');
            $table->string('nama_bank', 50)->nullable()->after('no_rekening');
            $table->string('npwp', 30)->nullable()->after('nama_bank');
            $table->string('no_karpeg', 30)->nullable()->after('npwp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['nik', 'no_rekening', 'nama_bank', 'npwp', 'no_karpeg']);
        });
    }
};
