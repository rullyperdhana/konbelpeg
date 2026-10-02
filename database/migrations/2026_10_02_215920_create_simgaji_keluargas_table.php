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
        Schema::create('simgaji_keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->index();
            $table->string('nmkel')->index();
            $table->string('kdhubkel', 10)->nullable();
            $table->string('hubungan', 50)->nullable();
            $table->string('kdjenkel', 5)->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->date('tgllhr')->nullable();
            $table->string('kdtunjang', 5)->nullable();
            $table->string('status_tunjangan', 50)->nullable();
            $table->string('kdstawin', 5)->nullable();
            $table->string('nipsuamiis', 50)->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('noaktalahi')->nullable();
            $table->string('nosks')->nullable();
            $table->date('tglsks')->nullable();
            $table->date('tglnikah')->nullable();
            $table->date('tglcerai')->nullable();
            $table->date('tglwafat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simgaji_keluargas');
    }
};
