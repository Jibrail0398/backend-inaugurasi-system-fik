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
        Schema::create('penerimaan_peserta', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('NIM', 16);
            $table->string('bukti_pembayaran'); // simpan path/file bukti pembayaran
            $table->date('tanggal_penerimaan')->nullable();
            $table->unsignedBigInteger('id_peserta')->nullable();
            $table->enum('status', ['pending', 'diterima', 'ditolak'])->default('pending');

            // relasi ke tabel peserta
            $table->foreign('id_peserta')
                  ->references('id')
                  ->on('peserta')
                  ->onDelete('cascade');

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_peserta');
    }
};