<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // N+1 query: melempar error saat pengembangan/pengujian, hanya dicatat di produksi.
        Model::preventLazyLoading();
        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relasi) {
                Log::warning('Lazy loading terdeteksi', ['model' => $model::class, 'relasi' => $relasi]);
            });

            URL::forceScheme('https');

            // Catat permintaan yang total waktu querynya lebih dari 2 detik.
            DB::whenQueryingForLongerThan(2000, function ($koneksi, $kejadian) {
                Log::warning('Query lambat', ['sql' => $kejadian->sql ?? null]);
            });
        }

        $this->aturBatasLaju();
    }

    private function aturBatasLaju(): void
    {
        // Lapisan kasar per IP; LoginRequest menambah batas per email+IP.
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(20)->by($r->ip()));

        // Unggah draf: 10 per menit per pengguna.
        RateLimiter::for('unggah', fn (Request $r) => Limit::perMinute(10)->by($r->user()?->id ?: $r->ip()));

        // API: lebih longgar untuk pengguna ber-token.
        RateLimiter::for('api', fn (Request $r) => $r->user()
            ? Limit::perMinute(120)->by($r->user()->id)
            : Limit::perMinute(60)->by($r->ip()));
    }
}
