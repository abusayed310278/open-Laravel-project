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
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $path = $request->file('logo')->store('branding', 'public');
        $this->settings->set('brand_logo', $path, 'branding');

        ActivityLog::record('settings.logo.updated');

        return back()->with('status', 'Site logo updated successfully.');
    }

    public function removeLogo(): RedirectResponse
    {
        $oldLogo = $this->settings->get('brand_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
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
        if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $path = $request->file('favicon')->store('branding', 'public');
        $this->settings->set('brand_favicon', $path, 'branding');

        ActivityLog::record('settings.siteicon.updated');

        return back()->with('status', 'Site icon (favicon) updated successfully.');
    }

    public function removeSiteicon(): RedirectResponse
    {
        $oldFavicon = $this->settings->get('brand_favicon');
        if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
            Storage::disk('public')->delete($oldFavicon);
        }

        $this->settings->set('brand_favicon', null, 'branding');
        ActivityLog::record('settings.siteicon.removed');

        return back()->with('status', 'Custom site icon removed. Default favicon restored.');
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
            $this->settings->set('brand_logo', $request->file('logo')->store('branding', 'public'), 'branding');
        }

        if ($request->hasFile('favicon')) {
            $this->settings->set('brand_favicon', $request->file('favicon')->store('branding', 'public'), 'branding');
        }

        $this->settings->set('brand_font', $request->string('brand_font')->value() ?: null, 'branding');
        $this->settings->set('brand_color_primary', $request->string('brand_color_primary')->value() ?: null, 'branding');

        ActivityLog::record('settings.branding.updated');

        return back()->with('status', 'Branding updated.');
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
        return view('admin.settings.storage', [
            'values' => [
                'r2_access_key_id' => $this->settings->get('r2_access_key_id'),
                'r2_bucket' => $this->settings->get('r2_bucket'),
                'r2_endpoint' => $this->settings->get('r2_endpoint'),
                'r2_url' => $this->settings->get('r2_url'),
                'r2_region' => $this->settings->get('r2_region', 'auto'),
                'storage_disk' => $this->settings->get('storage_disk', 'public'),
            ],
            'hasSecret' => $this->settings->has('r2_secret_access_key'),
        ]);
    }

    public function updateStorage(UpdateStorageRequest $request): RedirectResponse
    {
        $this->settings->setMany([
            'r2_access_key_id' => $request->string('r2_access_key_id')->value(),
            'r2_bucket' => $request->string('r2_bucket')->value(),
            'r2_endpoint' => $request->string('r2_endpoint')->value(),
            'r2_url' => $request->string('r2_url')->value() ?: null,
            'r2_region' => $request->string('r2_region')->value() ?: 'auto',
            'storage_disk' => $request->string('storage_disk')->value(),
        ], 'storage');

        if ($request->filled('r2_secret_access_key')) {
            $this->settings->set('r2_secret_access_key', $request->string('r2_secret_access_key')->value(), 'storage');
        }

        ActivityLog::record('settings.storage.updated');

        return back()->with('status', 'Storage settings updated.');
    }

    public function testStorage(): RedirectResponse
    {
        try {
            $path = 'openbox-connection-test.txt';
            Storage::disk('r2')->put($path, 'Openbox R2 connection test — '.now());
            Storage::disk('r2')->delete($path);
        } catch (Throwable $e) {
            return back()->withErrors(['r2_access_key_id' => 'Connection failed: '.$e->getMessage()]);
        }

        ActivityLog::record('settings.storage.tested');

        return back()->with('status', 'Successfully connected to Cloudflare R2.');
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
