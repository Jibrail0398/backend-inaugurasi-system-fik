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
        Schema::create('daftar_hadir_peserta', function (Blueprint $table) {
            $table->id();
            $table->enum('presensi_datang', ['hadir', 'tidak hadir']);
            $table->datetime('waktu_presensi_datang')->nullable();
            $table->enum('presensi_pulang', ['pulang', 'belum pulang']);
            $table->datetime('waktu_presensi_pulang')->nullable();
            $table->string('qr_code')->nullable();
            $table->foreignId('penerimaan_peserta_id')->constrained('penerimaan_peserta')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_hadir_peserta');
    }
};
