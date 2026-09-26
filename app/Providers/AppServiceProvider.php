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

        \Illuminate\Support\Facades\Storage::extend('cloudinary', function ($app, array $config) {
            $adapter = new \App\Support\CloudinaryAdapter($config);
            $flysystem = new \League\Flysystem\Filesystem($adapter, $config);

            return new \Illuminate\Filesystem\FilesystemAdapter($flysystem, $adapter, $config);
        });

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

        $r2Bucket = $settings->get('r2_bucket') ?: (config('filesystems.disks.r2.bucket') ?: 'r2-bucket');
        $r2Key = $settings->get('r2_access_key_id') ?: config('filesystems.disks.r2.key', '');
        $r2Secret = $settings->has('r2_secret_access_key') ? $settings->getDecrypted('r2_secret_access_key') : config('filesystems.disks.r2.secret', '');
        $r2Endpoint = $settings->get('r2_endpoint') ?: config('filesystems.disks.r2.endpoint');
        $r2Url = $settings->get('r2_url') ?: config('filesystems.disks.r2.url');

        config([
            'filesystems.disks.r2' => [
                'driver' => 's3',
                'key' => (string) $r2Key,
                'secret' => (string) $r2Secret,
                'region' => $settings->get('r2_region', 'auto'),
                'bucket' => (string) $r2Bucket,
                'url' => $r2Url,
                'endpoint' => $r2Endpoint,
                'use_path_style_endpoint' => false,
                'throw' => false,
            ],
        ]);

        if ($settings->has('cloudinary_cloud_name')) {
            $cloudName = $settings->get('cloudinary_cloud_name');
            $customUrl = $settings->get('cloudinary_url');
            config([
                'filesystems.disks.cloudinary' => [
                    'driver' => 'cloudinary',
                    'cloud_name' => $cloudName,
                    'api_key' => $settings->get('cloudinary_api_key'),
                    'api_secret' => $settings->has('cloudinary_api_secret') ? $settings->getDecrypted('cloudinary_api_secret') : null,
                    'url' => $customUrl ?: "https://res.cloudinary.com/{$cloudName}/image/upload",
                ],
            ]);
        }

        if ($settings->has('storage_cdn_url')) {
            config(['filesystems.disks.public.cdn_url' => $settings->get('storage_cdn_url')]);
        }

        $activeDisk = $settings->get('storage_disk', 'public');
        if (in_array($activeDisk, ['public', 'r2', 'cloudinary'], true)) {
            config(['filesystems.default' => $activeDisk]);
        }
    }
}
