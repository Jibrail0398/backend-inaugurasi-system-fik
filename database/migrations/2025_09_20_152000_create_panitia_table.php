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
        Schema::create('panitia', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('NIM', 16);
            $table->string('angkatan', 4)->nullable();
            $table->string('kelas')->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('nomor_whatsapp', 14)->nullable();
            $table->string('email')->unique();
            $table->string('ukuran_kaos')->nullable();
            $table->string('nomor_darurat', 14)->nullable();
            $table->string('tipe_nomor_darurat')->nullable();
            $table->string('riwayat_penyakit')->nullable();
            $table->string('divisi')->nullable();
            $table->enum('komitmen1', ['ya', 'tidak'])->default('tidak');
            $table->enum('komitmen2', ['ya', 'tidak'])->default('tidak');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panitia');
    }
};
