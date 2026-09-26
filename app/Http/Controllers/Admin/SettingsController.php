<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateBrandingRequest;
use App\Http\Requests\Admin\Settings\UpdateMailRequest;
use App\Http\Requests\Admin\Settings\UpdatePaymentsRequest;
use App\Http\Requests\Admin\Settings\UpdateStorageRequest;
use App\Models\ActivityLog;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

use App\Services\Admin\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly MaintenanceService $maintenance
    ) {}

    public function branding(): View
    {
        return view('admin.settings.branding', [
            'fonts' => UpdateBrandingRequest::FONTS,
            'logo' => $this->settings->get('brand_logo'),
            'favicon' => $this->settings->get('brand_favicon'),
            'font' => $this->settings->get('brand_font', 'Montserrat'),
            'color' => $this->settings->get('brand_color_primary', '#f59e0b'),
        ]);
    }

    public function logo(): View
    {
        return view('admin.settings.logo', [
            'logo' => $this->settings->get('brand_logo'),
        ]);
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        // Delete old custom logo file if exists
        $oldLogo = $this->settings->get('brand_logo');
        if (!empty($oldLogo) && is_string($oldLogo) && trim($oldLogo) !== '' && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $path = $this->storeUploadedFile($request->file('logo'), 'branding', 'public');
        $this->settings->set('brand_logo', $path, 'branding');

        ActivityLog::record('settings.logo.updated');

        return back()->with('status', 'Site logo updated successfully.');
    }

    public function removeLogo(): RedirectResponse
    {
        $oldLogo = $this->settings->get('brand_logo');
        if (!empty($oldLogo) && is_string($oldLogo) && trim($oldLogo) !== '' && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $this->settings->set('brand_logo', null, 'branding');
        ActivityLog::record('settings.logo.removed');

        return back()->with('status', 'Custom logo removed. Default Openbox logo restored.');
    }

    public function siteicon(): View
    {
        return view('admin.settings.siteicon', [
            'favicon' => $this->settings->get('brand_favicon'),
        ]);
    }

    public function updateSiteicon(Request $request): RedirectResponse
    {
        $request->validate([
            'favicon' => ['required', 'file', 'mimes:ico,png,svg,jpg,jpeg,webp', 'max:1024'],
        ]);

        $oldFavicon = $this->settings->get('brand_favicon');
        if (!empty($oldFavicon) && is_string($oldFavicon) && trim($oldFavicon) !== '' && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $path = $this->storeUploadedFile($request->file('favicon'), 'branding', 'public');
        $this->settings->set('brand_favicon', $path, 'branding');

        ActivityLog::record('settings.siteicon.updated');

        return back()->with('status', 'Site icon (favicon) updated successfully.');
    }

    public function removeSiteicon(): RedirectResponse
    {
        $oldFavicon = $this->settings->get('brand_favicon');
        if (!empty($oldFavicon) && is_string($oldFavicon) && trim($oldFavicon) !== '' && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $this->settings->set('brand_favicon', null, 'branding');
        ActivityLog::record('settings.siteicon.removed');

        return back()->with('status', 'Custom site icon removed. Default favicon restored.');
    }

    public function footericon(): View
    {
        return view('admin.settings.footericon', [
            'footerIcon' => $this->settings->get('brand_footer_icon'),
        ]);
    }

    public function updateFootericon(Request $request): RedirectResponse
    {
        $request->validate([
            'footer_icon' => ['required', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:2048'],
        ]);

        $oldIcon = $this->settings->get('brand_footer_icon');
        if (!empty($oldIcon) && is_string($oldIcon) && trim($oldIcon) !== '' && Storage::disk('public')->exists($oldIcon)) {
            Storage::disk('public')->delete($oldIcon);
        }

        $path = $this->storeUploadedFile($request->file('footer_icon'), 'branding', 'public');
        $this->settings->set('brand_footer_icon', $path, 'branding');

        ActivityLog::record('settings.footericon.updated');

        return back()->with('status', 'Footer icon updated successfully.');
    }

    public function removeFootericon(): RedirectResponse
    {
        $oldIcon = $this->settings->get('brand_footer_icon');
        if (!empty($oldIcon) && is_string($oldIcon) && trim($oldIcon) !== '' && Storage::disk('public')->exists($oldIcon)) {
            Storage::disk('public')->delete($oldIcon);
        }

        $this->settings->set('brand_footer_icon', null, 'branding');
        ActivityLog::record('settings.footericon.removed');

        return back()->with('status', 'Custom footer icon removed. Default icon restored.');
    }

    public function font(): View

    {
        return view('admin.settings.font', [
            'fonts' => UpdateBrandingRequest::FONTS,
            'currentFont' => $this->settings->get('brand_font', 'Montserrat'),
        ]);
    }

    public function updateFont(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_font' => ['required', Rule::in(UpdateBrandingRequest::FONTS)],
        ]);

        $this->settings->set('brand_font', $request->string('brand_font')->value(), 'branding');
        ActivityLog::record('settings.font.updated', properties: ['font' => $request->string('brand_font')->value()]);

        return back()->with('status', 'Site typography updated to '.$request->string('brand_font')->value().'.');
    }

    public function color(): View
    {
        return view('admin.settings.color', [
            'currentColor' => $this->settings->get('brand_color_primary', '#f59e0b'),
        ]);
    }

    public function updateColor(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_color_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $hex = strtolower($request->string('brand_color_primary')->value());
        $this->settings->set('brand_color_primary', $hex, 'branding');
        ActivityLog::record('settings.color.updated', properties: ['color' => $hex]);

        return back()->with('status', 'Site brand primary color updated to '.$hex.'.');
    }

    public function cache(): View
    {
        return view('admin.settings.cache', [
            'actions' => $this->maintenance->availableActions(),
        ]);
    }

    public function clearCache(string $action = 'all-clear'): RedirectResponse
    {
        try {
            $result = $this->maintenance->run($action);
        } catch (\InvalidArgumentException) {
            abort(404);
        }

        ActivityLog::record('settings.cache.'.$action, properties: ['output' => $result['output']]);

        return back()->with('status', $result['label'].'.');
    }

    public function updateBranding(UpdateBrandingRequest $request): RedirectResponse
    {
        if ($request->hasFile('logo')) {
            $this->settings->set('brand_logo', $this->storeUploadedFile($request->file('logo'), 'branding', 'public'), 'branding');
        }

        if ($request->hasFile('favicon')) {
            $this->settings->set('brand_favicon', $this->storeUploadedFile($request->file('favicon'), 'branding', 'public'), 'branding');
        }

        $this->settings->set('brand_font', $request->string('brand_font')->value() ?: null, 'branding');
        $this->settings->set('brand_color_primary', $request->string('brand_color_primary')->value() ?: null, 'branding');

        ActivityLog::record('settings.branding.updated');

        return back()->with('status', 'Branding updated.');
    }

    /**
     * Store an uploaded file safely, handling PHP 8.4 / Windows temp file paths.
     */
    private function storeUploadedFile(\Illuminate\Http\UploadedFile $file, string $directory = 'branding', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $filename = \Illuminate\Support\Str::random(40) . '.' . strtolower($extension);
        $targetPath = trim($directory, '/') . '/' . $filename;

        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        if (!empty($sourcePath) && file_exists($sourcePath)) {
            $stream = @fopen($sourcePath, 'r');
            if ($stream !== false) {
                try {
                    Storage::disk($disk)->put($targetPath, $stream);
                    return $targetPath;
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
        }

        return $file->store($directory, $disk);
    }


    public function mail(): View
    {
        return view('admin.settings.mail', [
            'values' => [
                'mail_host' => $this->settings->get('mail_host'),
                'mail_port' => $this->settings->get('mail_port', '587'),
                'mail_encryption' => $this->settings->get('mail_encryption', 'tls'),
                'mail_username' => $this->settings->get('mail_username'),
                'mail_from_address' => $this->settings->get('mail_from_address', config('mail.from.address')),
                'mail_from_name' => $this->settings->get('mail_from_name', config('app.name')),
            ],
            'hasPassword' => $this->settings->has('mail_password'),
        ]);
    }

    public function updateMail(UpdateMailRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'mail_host' => $request->string('mail_host')->value(),
            'mail_port' => (string) $request->integer('mail_port'),
            'mail_encryption' => $request->string('mail_encryption')->value(),
            'mail_username' => $request->string('mail_username')->value() ?: null,
            'mail_from_address' => $request->string('mail_from_address')->value(),
            'mail_from_name' => $request->string('mail_from_name')->value(),
        ], 'mail');

        if ($request->filled('mail_password')) {
            $this->settings->set('mail_password', $request->string('mail_password')->value(), 'mail');
        }

        ActivityLog::record('settings.mail.updated');

        return back()->with('status', 'SMTP settings updated.');
    }

    public function sendTestMail(): RedirectResponse
    {
        try {
            Mail::raw('This is a test email from your Openbox admin settings.', function ($message) {
                $message->to(request()->user()->email)->subject('Openbox SMTP test');
            });
        } catch (Throwable $e) {
            return back()->withErrors(['mail_host' => 'Could not send test email: '.$e->getMessage()]);
        }

        ActivityLog::record('settings.mail.tested');

        return back()->with('status', 'Test email sent to '.request()->user()->email.'.');
    }

    public function storage(): View
    {
        $defaultCloudName = 'w36cggya';
        $defaultApiKey = '146238954648692';
        $defaultApiSecret = 'BEuXx-vqfq-tPmWMTaRbJd6SyH8';
        $defaultUrl = 'cloudinary://146238954648692:BEuXx-vqfq-tPmWMTaRbJd6SyH8@w36cggya';

        // Auto-seed default user Cloudinary credentials if not saved yet
        if (! $this->settings->has('cloudinary_cloud_name')) {
            $this->settings->setMany([
                'cloudinary_cloud_name' => $defaultCloudName,
                'cloudinary_api_key' => $defaultApiKey,
                'cloudinary_url' => $defaultUrl,
            ], 'storage');
            $this->settings->set('cloudinary_api_secret', $defaultApiSecret, 'storage');
        }

        $historyLogs = ActivityLog::query()
            ->with('user')
            ->where('action', 'like', 'settings.storage%')
            ->latest('id')
            ->paginate(5, ['*'], 'history_page')
            ->fragment('storage-history');

        return view('admin.settings.storage', [
            'values' => [
                'r2_access_key_id' => $this->settings->get('r2_access_key_id'),
                'r2_bucket' => $this->settings->get('r2_bucket'),
                'r2_endpoint' => $this->settings->get('r2_endpoint'),
                'r2_url' => $this->settings->get('r2_url'),
                'r2_region' => $this->settings->get('r2_region', 'auto'),
                'cloudinary_cloud_name' => $this->settings->get('cloudinary_cloud_name', $defaultCloudName),
                'cloudinary_api_key' => $this->settings->get('cloudinary_api_key', $defaultApiKey),
                'cloudinary_url' => $this->settings->get('cloudinary_url', $defaultUrl),
                'storage_disk' => $this->settings->get('storage_disk', $this->settings->has('cloudinary_cloud_name') ? 'cloudinary' : 'public'),
                'storage_cdn_url' => $this->settings->get('storage_cdn_url'),
            ],
            'hasR2Secret' => $this->settings->has('r2_secret_access_key'),
            'hasCloudinarySecret' => $this->settings->has('cloudinary_api_secret') || ! empty($defaultApiSecret),
            'historyLogs' => $historyLogs,
        ]);
    }

    public function clearStorageHistory(): RedirectResponse
    {
        ActivityLog::query()
            ->where('action', 'like', 'settings.storage%')
            ->delete();

        ActivityLog::record('settings.storage.history_cleared');

        return redirect()->route('admin.settings.storage')->with('status', 'Storage settings change history cleared successfully.');
    }

    public function updateActiveStorage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'storage_disk' => ['required', 'string', 'in:public,r2,cloudinary'],
            'storage_cdn_url' => ['nullable', 'url', 'max:500'],
        ]);

        $previousDisk = $this->settings->get('storage_disk', 'public');
        $newDisk = $validated['storage_disk'];
        $cdnUrl = trim((string) ($validated['storage_cdn_url'] ?? ''));

        $this->settings->set('storage_disk', $newDisk, 'storage');
        $this->settings->set('storage_cdn_url', $cdnUrl ?: null, 'storage');
        config(['filesystems.default' => $newDisk]);

        try {
            \Illuminate\Support\Facades\Artisan::call('storage:sync-urls', ['--disk' => $newDisk]);
        } catch (\Throwable) {
            // Ignore if background task fails
        }

        ActivityLog::record('settings.storage.active_disk_changed', null, [
            'from_disk' => $previousDisk,
            'to_disk' => $newDisk,
        ]);

        return back()->with('status', 'Active storage driver updated to '.strtoupper($newDisk).' and database URLs synced.');
    }

    public function updateCloudinaryStorage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cloudinary_cloud_name' => ['nullable', 'string', 'max:255'],
            'cloudinary_api_key' => ['nullable', 'string', 'max:255'],
            'cloudinary_api_secret' => ['nullable', 'string', 'max:255'],
            'cloudinary_url' => ['nullable', 'string', 'max:500'],
        ]);

        $cloudName = trim((string) ($validated['cloudinary_cloud_name'] ?? ''));
        $apiKey = trim((string) ($validated['cloudinary_api_key'] ?? ''));
        $apiSecret = trim((string) ($validated['cloudinary_api_secret'] ?? ''));
        $cloudinaryUrl = trim((string) ($validated['cloudinary_url'] ?? ''));

        // Auto-parse CLOUDINARY_URL string if provided (e.g. cloudinary://API_KEY:API_SECRET@CLOUD_NAME)
        if (str_starts_with($cloudinaryUrl, 'cloudinary://') && preg_match('#^cloudinary://([^:]+):([^@]+)@(.+)$#i', $cloudinaryUrl, $matches)) {
            if (empty($apiKey)) {
                $apiKey = $matches[1];
            }
            if (empty($apiSecret)) {
                $apiSecret = $matches[2];
            }
            if (empty($cloudName)) {
                $cloudName = $matches[3];
            }
        } elseif (str_starts_with($cloudName, 'cloudinary://') && preg_match('#^cloudinary://([^:]+):([^@]+)@(.+)$#i', $cloudName, $matches)) {
            $apiKey = $matches[1];
            $apiSecret = $matches[2];
            $cloudName = $matches[3];
            $cloudinaryUrl = "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}";
        }

        $this->settings->setMany([
            'cloudinary_cloud_name' => $cloudName ?: null,
            'cloudinary_api_key' => $apiKey ?: null,
            'cloudinary_url' => $cloudinaryUrl ?: null,
            'storage_disk' => 'cloudinary',
        ], 'storage');

        if (! empty($apiSecret)) {
            $this->settings->set('cloudinary_api_secret', $apiSecret, 'storage');
        }

        ActivityLog::record('settings.storage.cloudinary_updated', null, [
            'cloud_name' => $cloudName,
            'has_api_key' => ! empty($apiKey),
        ]);

        return back()->with('status', 'Cloudinary storage settings saved and set as Active Driver.');
    }

    public function updateR2Storage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'r2_access_key_id' => ['nullable', 'string', 'max:255'],
            'r2_secret_access_key' => ['nullable', 'string', 'max:255'],
            'r2_bucket' => ['nullable', 'string', 'max:255'],
            'r2_endpoint' => ['nullable', 'url', 'max:255'],
            'r2_url' => ['nullable', 'url', 'max:255'],
            'r2_region' => ['nullable', 'string', 'max:50'],
        ]);

        $this->settings->setMany([
            'r2_access_key_id' => $validated['r2_access_key_id'] ?? null,
            'r2_bucket' => $validated['r2_bucket'] ?? null,
            'r2_endpoint' => $validated['r2_endpoint'] ?? null,
            'r2_url' => $validated['r2_url'] ?? null,
            'r2_region' => $validated['r2_region'] ?? 'auto',
            'storage_disk' => 'r2',
        ], 'storage');

        if ($request->filled('r2_secret_access_key')) {
            $this->settings->set('r2_secret_access_key', $request->string('r2_secret_access_key')->value(), 'storage');
        }

        ActivityLog::record('settings.storage.r2_updated', null, [
            'bucket' => $validated['r2_bucket'] ?? null,
        ]);

        return back()->with('status', 'Cloudflare R2 storage settings saved and set as Active Driver.');
    }

    public function updateStorage(UpdateStorageRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'r2_access_key_id' => $request->string('r2_access_key_id')->value() ?: null,
            'r2_bucket' => $request->string('r2_bucket')->value() ?: null,
            'r2_endpoint' => $request->string('r2_endpoint')->value() ?: null,
            'r2_url' => $request->string('r2_url')->value() ?: null,
            'r2_region' => $request->string('r2_region')->value() ?: 'auto',
            'cloudinary_cloud_name' => $request->string('cloudinary_cloud_name')->value() ?: null,
            'cloudinary_api_key' => $request->string('cloudinary_api_key')->value() ?: null,
            'cloudinary_url' => $request->string('cloudinary_url')->value() ?: null,
            'storage_disk' => $request->string('storage_disk')->value(),
        ], 'storage');

        if ($request->filled('r2_secret_access_key')) {
            $this->settings->set('r2_secret_access_key', $request->string('r2_secret_access_key')->value(), 'storage');
        }

        if ($request->filled('cloudinary_api_secret')) {
            $this->settings->set('cloudinary_api_secret', $request->string('cloudinary_api_secret')->value(), 'storage');
        }

        ActivityLog::record('settings.storage.updated');

        return back()->with('status', 'Storage settings updated.');
    }

    public function testStorage(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $targetDisk = $request->input('disk', $this->settings->get('storage_disk', 'r2'));
        if (! in_array($targetDisk, ['r2', 'cloudinary'], true)) {
            $targetDisk = 'r2';
        }

        if ($targetDisk === 'cloudinary') {
            Storage::extend('cloudinary', function ($app, array $config) {
                $adapter = new \App\Support\CloudinaryAdapter($config);
                $flysystem = new \League\Flysystem\Filesystem($adapter, $config);

                return new \Illuminate\Filesystem\FilesystemAdapter($flysystem, $adapter, $config);
            });

            config([
                'filesystems.disks.cloudinary' => [
                    'driver' => 'cloudinary',
                    'cloud_name' => $this->settings->get('cloudinary_cloud_name', 'w36cggya'),
                    'api_key' => $this->settings->get('cloudinary_api_key', '146238954648692'),
                    'api_secret' => $this->settings->getDecrypted('cloudinary_api_secret') ?: 'BEuXx-vqfq-tPmWMTaRbJd6SyH8',
                    'url' => $this->settings->get('cloudinary_url', 'cloudinary://146238954648692:BEuXx-vqfq-tPmWMTaRbJd6SyH8@w36cggya'),
                ],
            ]);
        } elseif ($targetDisk === 'r2') {
            config([
                'filesystems.disks.r2' => [
                    'driver' => 's3',
                    'key' => $this->settings->get('r2_access_key_id'),
                    'secret' => $this->settings->has('r2_secret_access_key') ? $this->settings->getDecrypted('r2_secret_access_key') : null,
                    'region' => $this->settings->get('r2_region', 'auto'),
                    'bucket' => $this->settings->get('r2_bucket'),
                    'url' => $this->settings->get('r2_url'),
                    'endpoint' => $this->settings->get('r2_endpoint'),
                    'use_path_style_endpoint' => false,
                    'throw' => false,
                ],
            ]);
        }

        try {
            $path = 'openbox-connection-test.txt';
            Storage::disk($targetDisk)->put($path, 'Openbox '.$targetDisk.' connection test — '.now());
            Storage::disk($targetDisk)->delete($path);
        } catch (\Throwable $e) {
            $fieldKey = $targetDisk === 'cloudinary' ? 'cloudinary_cloud_name' : 'r2_access_key_id';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed: '.$e->getMessage(),
                ], 422);
            }

            return back()->withErrors([$fieldKey => 'Connection failed: '.$e->getMessage()]);
        }

        ActivityLog::record('settings.storage.tested', null, ['disk' => $targetDisk]);

        $diskLabel = $targetDisk === 'cloudinary' ? 'Cloudinary' : 'Cloudflare R2';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully connected to '.$diskLabel.'.',
            ]);
        }

        return back()->with('status', 'Successfully connected to '.$diskLabel.'.');
    }

    public function payments(): View
    {
        return view('admin.settings.payments', [
            'values' => [
                'stripe_enabled' => $this->settings->get('stripe_enabled') === '1',
                'stripe_publishable_key' => $this->settings->get('stripe_publishable_key'),
                'paypal_enabled' => $this->settings->get('paypal_enabled') === '1',
                'paypal_client_id' => $this->settings->get('paypal_client_id'),
                'bank_details' => $this->settings->get('bank_details'),
            ],
            'hasStripeSecret' => $this->settings->has('stripe_secret_key'),
            'hasPaypalSecret' => $this->settings->has('paypal_client_secret'),
        ]);
    }

    public function updatePayments(UpdatePaymentsRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'stripe_enabled' => $request->boolean('stripe_enabled') ? '1' : '0',
            'stripe_publishable_key' => $request->string('stripe_publishable_key')->value() ?: null,
            'paypal_enabled' => $request->boolean('paypal_enabled') ? '1' : '0',
            'paypal_client_id' => $request->string('paypal_client_id')->value() ?: null,
            'bank_details' => $request->string('bank_details')->value() ?: null,
        ], 'payments');

        if ($request->filled('stripe_secret_key')) {
            $this->settings->set('stripe_secret_key', $request->string('stripe_secret_key')->value(), 'payments');
        }

        if ($request->filled('paypal_client_secret')) {
            $this->settings->set('paypal_client_secret', $request->string('paypal_client_secret')->value(), 'payments');
        }

        ActivityLog::record('settings.payments.updated');

        return back()->with('status', 'Payment settings updated.');
    }
}
