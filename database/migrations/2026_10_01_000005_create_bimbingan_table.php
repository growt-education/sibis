<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bimbingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skripsi_id')->constrained('skripsi')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen')->restrictOnDelete();
            $table->date('tanggal');
            $table->string('bab', 50);
            $table->text('uraian');
            $table->text('catatan_dosen')->nullable();
            $table->string('file_draf', 255)->nullable();
            $table->enum('status', ['menunggu', 'revisi', 'disetujui'])->default('menunggu');
            $table->timestamps();

            $table->index(['skripsi_id', 'tanggal']);
            $table->index(['skripsi_id', 'status']);   // hitung bimbingan disetujui
            $table->index(['dosen_id', 'status']);     // antrean pemeriksaan dosen
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan');
    }
};
