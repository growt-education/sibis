<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('peran', ['koordinator', 'dosen', 'mahasiswa'])
                ->default('mahasiswa')->after('email');
            // true = pengguna wajib mengganti kata sandi awal sebelum memakai aplikasi
            $table->boolean('wajib_ganti_password')->default(false)->after('password');
            $table->index('peran');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['peran']);
            $table->dropColumn(['peran', 'wajib_ganti_password']);
        });
    }
};
