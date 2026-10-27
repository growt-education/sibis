<?php

namespace App\Enums;

enum StatusBimbingan: string
{
    case Menunggu = 'menunggu';
    case Revisi = 'revisi';
    case Disetujui = 'disetujui';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu diperiksa',
            self::Revisi => 'Perlu revisi',
            self::Disetujui => 'Disetujui',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Menunggu => 'secondary',
            self::Revisi => 'warning',
            self::Disetujui => 'success',
        };
    }
}
