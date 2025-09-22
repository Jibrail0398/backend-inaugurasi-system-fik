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
        Schema::create('uang_keluar', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah_pengeluaran')->nullable();
            $table->string('alasan_pengeluaran')->nullable();
            $table->date('tanggal_pengeluaran')->nullable();
            $table->string('bukti_pengeluaran')->nullable();
            $table->foreignId('keuangan_id')->constrained('keuangan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uang_keluar');
    }
};
