<?php

namespace App\Enums;

enum StatusSkripsi: string
{
    case Diajukan = 'diajukan';
    case Bimbingan = 'bimbingan';
    case SeminarProposal = 'seminar_proposal';
    case SeminarHasil = 'seminar_hasil';
    case Ujian = 'ujian';
    case Lulus = 'lulus';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::Bimbingan => 'Bimbingan',
            self::SeminarProposal => 'Seminar proposal',
            self::SeminarHasil => 'Seminar hasil',
            self::Ujian => 'Ujian',
            self::Lulus => 'Lulus',
        };
    }

    /** Kelas warna badge Bootstrap. */
    public function warna(): string
    {
        return match ($this) {
            self::Diajukan => 'secondary',
            self::Bimbingan => 'primary',
            self::SeminarProposal, self::SeminarHasil => 'info',
            self::Ujian => 'warning',
            self::Lulus => 'success',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
