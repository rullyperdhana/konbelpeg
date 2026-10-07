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
        Schema::create('audit_tunjangan_resolusis', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->index();
            $table->string('kunci_kasus', 255)->unique();
            $table->string('status', 20)->default('selesai')->index();
            $table->string('no_sts', 100)->nullable();
            $table->date('tgl_sts')->nullable();
            $table->decimal('nominal_pengembalian', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolved_by_name', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_tunjangan_resolusis');
    }
};
