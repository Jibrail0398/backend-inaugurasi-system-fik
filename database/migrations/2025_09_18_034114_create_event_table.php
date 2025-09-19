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
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->string('nama_event');
            $table->string('jenis');
            $table->string('tema');
            $table->string('tempat');
            $table->enum('status_pendaftaran_panitia', ['buka', 'tutup'])->default('tutup');
            $table->enum('status_pendaftaran_peserta', ['buka', 'tutup'])->default('tutup');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
