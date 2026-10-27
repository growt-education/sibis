<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()
                ->constrained('users')->nullOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('nama', 150);
            $table->unsignedSmallInteger('angkatan');
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->timestamps();

            $table->index('angkatan');
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
