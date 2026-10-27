<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skripsi', function (Blueprint $table) {
            $table->id();
            // unik: satu mahasiswa hanya boleh punya satu skripsi (dijaga di level database)
            $table->foreignId('mahasiswa_id')->unique()->constrained('mahasiswa')->restrictOnDelete();
            $table->foreignId('bidang_minat_id')->constrained('bidang_minat')->restrictOnDelete();
            $table->string('judul', 255);
            $table->text('ringkasan')->nullable();
            $table->foreignId('pembimbing_1_id')->nullable()->constrained('dosen')->restrictOnDelete();
            $table->foreignId('pembimbing_2_id')->nullable()->constrained('dosen')->restrictOnDelete();
            $table->enum('status', [
                'diajukan', 'bimbingan', 'seminar_proposal', 'seminar_hasil', 'ujian', 'lulus',
            ])->default('diajukan');
            $table->date('tanggal_sk')->nullable();
            $table->timestamps();

            // Indeks untuk daftar/penyaringan dan penghitungan kuota dosen.
            $table->index('status');
            $table->index('bidang_minat_id');
            $table->index(['pembimbing_1_id', 'status']);
            $table->index(['pembimbing_2_id', 'status']);
        });

        // Aturan bisnis #2 dijaga juga oleh database (SQLite tidak mendukung ADD CONSTRAINT).
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE skripsi ADD CONSTRAINT skripsi_pembimbing_berbeda '
                .'CHECK (pembimbing_1_id IS NULL OR pembimbing_2_id IS NULL OR pembimbing_1_id <> pembimbing_2_id)'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('skripsi');
    }
};
