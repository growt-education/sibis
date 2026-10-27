<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Throwable;

/** Kirim notifikasi tanpa menggagalkan permintaan pengguna bila antrean/email bermasalah. */
class Beritahu
{
    public static function kirim(?User $penerima, Notification $notifikasi): void
    {
        if ($penerima === null) {
            return;
        }

        try {
            $penerima->notify($notifikasi);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
