<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        if (! $this->app->runningInConsole() && method_exists(\Illuminate\Support\Facades\Auth::guard('web'), 'setRememberDuration')) {
            \Illuminate\Support\Facades\Auth::guard('web')->setRememberDuration(43200); // 30 days (30 * 24 * 60)
        }

        $this->applyRuntimeSettings();
    }

    /**
     * Let admin-configured SMTP and Cloudflare R2 settings override the
     * .env defaults at runtime. Guarded so a fresh install (no `settings`
     * table yet, or no DB connection during early Artisan commands) never
     * breaks the boot cycle.
     */
    private function applyRuntimeSettings(): void
    {
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            // Artisan commands (migrate, key:generate, etc.) run before the
            // settings table necessarily exists — skip there, HTTP requests
            // and queued jobs still get the override via the service.
            return;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (QueryException) {
            return;
        }

        /** @var SettingsService $settings */
        $settings = $this->app->make(SettingsService::class);

        if ($settings->has('mail_host')) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings->get('mail_host'),
                'mail.mailers.smtp.port' => $settings->get('mail_port'),
                'mail.mailers.smtp.encryption' => $settings->get('mail_encryption'),
                'mail.mailers.smtp.username' => $settings->get('mail_username'),
                'mail.mailers.smtp.password' => $settings->has('mail_password') ? $settings->getDecrypted('mail_password') : null,
                'mail.from.address' => $settings->get('mail_from_address', config('mail.from.address')),
                'mail.from.name' => $settings->get('mail_from_name', config('mail.from.name')),
            ]);
        }

        if ($settings->has('r2_access_key_id')) {
            config([
                'filesystems.disks.r2' => [
                    'driver' => 's3',
                    'key' => $settings->get('r2_access_key_id'),
                    'secret' => $settings->has('r2_secret_access_key') ? $settings->getDecrypted('r2_secret_access_key') : null,
                    'region' => $settings->get('r2_region', 'auto'),
                    'bucket' => $settings->get('r2_bucket'),
                    'url' => $settings->get('r2_url'),
                    'endpoint' => $settings->get('r2_endpoint'),
                    'use_path_style_endpoint' => false,
                    'throw' => false,
                ],
            ]);

            if ($settings->get('storage_disk') === 'r2') {
                config(['filesystems.default' => 'r2']);
            }
        }
    }
}
