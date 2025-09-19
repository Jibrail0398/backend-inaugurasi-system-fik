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
        Schema::create('penerimaan_panitia', function (Blueprint $table) {
            $table->id();
            $table->enum('status_penerimaan', ['diterima', 'tidak diterima']);
            $table->date('tanggal_penerimaan');
            $table->foreignId('pendaftaran_panitia_id')->constrained('pendaptar_panitia')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_panitia');
    }
};
