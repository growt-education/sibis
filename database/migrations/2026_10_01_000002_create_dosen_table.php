<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()
                ->constrained('users')->nullOnDelete();
            $table->string('nidn', 20)->unique();
            $table->string('nama', 150);
            $table->foreignId('bidang_minat_id')->constrained('bidang_minat')->restrictOnDelete();
            $table->string('email', 255)->unique();
            $table->string('no_hp', 20)->nullable();
            $table->unsignedTinyInteger('kuota')->default(8);
            $table->timestamps();

            // PostgreSQL tidak membuat indeks otomatis untuk foreign key.
            $table->index('bidang_minat_id');
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
