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
        Schema::create('peserta', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('NIM')->length(16);
            $table->string('angkatan')->length(4);
            $table->string('kelas');
            $table->date('tanggal_lahir');
            $table->string('nomor_whatsapp')->length(14);
            $table->string('email')->unique();
            $table->string('ukuran_kaos');
            $table->string('nomor_darurat')->length(14);
            $table->string('tipe_nomor_darurat');
            $table->string('riwayat_penyakit');
            $table->string('divisi');
            $table->string('bukti_pembayaran');
            $table->enum('komitmen1', ['ya', 'tidak']);
            $table->enum('komitmen2', ['ya', 'tidak']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
