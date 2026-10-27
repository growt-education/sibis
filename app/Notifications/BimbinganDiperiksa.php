<?php

namespace App\Notifications;

use App\Enums\StatusBimbingan;
use App\Models\Bimbingan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Dikirim ke mahasiswa ketika dosen menyetujui atau meminta revisi. */
class BimbinganDiperiksa extends Notification implements ShouldQueue
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Bimbingan $bimbingan)
    {
    }

    public function via(object $notifiable): array
    {
        return config('sibis.notifikasi_email') ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->bimbingan->loadMissing('dosen');

        $surat = (new MailMessage)
            ->subject($b->status === StatusBimbingan::Disetujui
                ? 'Bimbingan Anda disetujui'
                : 'Bimbingan Anda perlu direvisi')
            ->greeting('Halo, '.$notifiable->name)
            ->line($b->dosen->nama.' telah memeriksa bimbingan "'.$b->bab.'": '.mb_strtolower($b->status->label()).'.');

        if ($b->catatan_dosen) {
            $surat->line('Catatan dosen: '.$b->catatan_dosen);
        }

        return $surat->action('Buka kartu bimbingan', route('skripsi.show', $b->skripsi_id));
    }
}
