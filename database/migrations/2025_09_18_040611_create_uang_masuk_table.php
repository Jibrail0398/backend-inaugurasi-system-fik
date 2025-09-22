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
        Schema::create('uang_masuk', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah_uang_masuk')->nullable();
            $table->string('asal_pemasukan')->nullable();
            $table->date('tanggal_pemasukan')->nullable();
            $table->string('bukti_pemasukan')->nullable();
            $table->foreignId('keuangan_id')->constrained('keuangan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uang_masuk');
    }
};
