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
        Schema::create('daptar_hadir_panitia', function (Blueprint $table) {
            $table->id();
            $table->enum('presensi_datang', ['hadir', 'tidak hadir','belum cek'])->default('belum cek');
            $table->datetime('waktu_presensi_datang')->nullable();
            $table->enum('presensi_pulang', ['pulang', 'belum pulang'])->default('belum pulang');
            $table->datetime('waktu_presensi_pulang')->nullable();
            $table->string('qr_code_datang')->nullable();
            $table->string('qr_code_pulang')->nullable();
            $table->foreignId('penerimaan_panitia_id')->constrained('penerimaan_panitia')->onDelete('cascade');
            $table->foreignId('create_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('update_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daptar_hadir_panitia');
    }
};
