<?php

namespace App\Notifications;

use App\Models\Bimbingan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Dikirim ke dosen ketika mahasiswa mencatat bimbingan baru. Diproses lewat antrean. */
class BimbinganBaru extends Notification implements ShouldQueue
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
        $b = $this->bimbingan->loadMissing('skripsi.mahasiswa');

        return (new MailMessage)
            ->subject('Catatan bimbingan baru dari '.$b->skripsi->mahasiswa->nama)
            ->greeting('Halo, '.$notifiable->name)
            ->line($b->skripsi->mahasiswa->nama.' mencatat bimbingan "'.$b->bab.'" dan menunggu pemeriksaan Anda.')
            ->action('Periksa bimbingan', route('skripsi.show', $b->skripsi_id));
    }
}
