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
        Schema::create('dokumentasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dokumentasi');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('link_drive')->nullable();
            $table->foreignId('event_id')->constrained('event')->onDelete('cascade');
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
        Schema::dropIfExists('dokumentasi');
    }
};
