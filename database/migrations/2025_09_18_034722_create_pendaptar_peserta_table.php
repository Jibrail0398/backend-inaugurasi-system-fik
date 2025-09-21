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
        Schema::create('pendaptar_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('event')->onDelete('cascade');
            $table->string('kode_peserta')->unique();
            $table->string('nama');
            $table->string('NIM')->length(16);
            $table->string('email')->nullable();
            $table->string('nomor_whatapp')->length(14);
            $table->string('angkatan')->length(4);
            $table->string('kelas');
            $table->date('tanggal_lahir');
            $table->string('ukuran_kaos');
            $table->string('nomor_darurat')->length(14);
            $table->string('tipe_nomor_darurat');
            $table->string('riwayat_penyakit');
            $table->string('divisi');
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaptar_peserta');
    }
};
