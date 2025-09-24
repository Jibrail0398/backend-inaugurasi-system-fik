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
            $table->string('kode_event')->unique()->nullable();
            $table->string('nama_event');
            $table->string('jenis');
            $table->string('tema');
            $table->string('tempat');
            $table->integer('harga_pendaftaran_peserta')->default(0);
            $table->enum('status_pendaftaran_panitia', ['buka', 'tutup'])->default('tutup');
            $table->enum('status_pendaftaran_peserta', ['buka', 'tutup'])->default('tutup');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('cascade');

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
