<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\SettingsService;
use App\Support\HomeContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeContentController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function whyBuy(): View
    {
        return view('admin.settings.why-buy', ['content' => HomeContent::whyBuy()]);
    }

    public function updateWhyBuy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'heading' => ['required', 'string', 'max:120'],
            'items' => ['required', 'array', 'size:4'],
            'items.*.title' => ['required', 'string', 'max:80'],
            'items.*.text' => ['required', 'string', 'max:160'],
        ]);

        $values = ['home_whybuy_heading' => $data['heading']];
        foreach (array_values($data['items']) as $index => $item) {
            $number = $index + 1;
            $values["home_whybuy_{$number}_title"] = $item['title'];
            $values["home_whybuy_{$number}_text"] = $item['text'];
        }

        $this->settings->setMany($values, 'home');
        ActivityLog::record('settings.home_why_buy.updated');

        return back()->with('status', 'Why Buy section updated successfully.');
    }

    public function promo(): View
    {
        return view('admin.settings.promo', [
            'cards' => HomeContent::promoCards(),
            'products' => Product::query()->live()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function updatePromo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cards' => ['required', 'array', 'size:3'],
            'cards.*.title' => ['required', 'string', 'max:120'],
            'cards.*.text' => ['required', 'string', 'max:200'],
            'cards.*.button' => ['required', 'string', 'max:40'],
            'cards.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $values = [];
        foreach (array_values($data['cards']) as $index => $card) {
            $number = $index + 1;
            $values["home_promo_{$number}_title"] = $card['title'];
            $values["home_promo_{$number}_text"] = $card['text'];
            $values["home_promo_{$number}_button"] = $card['button'];
            $values["home_promo_{$number}_product"] = isset($card['product_id']) ? (string) $card['product_id'] : '';
        }

        $this->settings->setMany($values, 'home');
        ActivityLog::record('settings.home_promo.updated');

        return back()->with('status', 'Promo cards updated successfully.');
    }

    public function newsletter(): View
    {
        return view('admin.settings.newsletter', ['content' => HomeContent::newsletter()]);
    }

    public function updateNewsletter(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'text' => ['required', 'string', 'max:160'],
            'placeholder' => ['required', 'string', 'max:60'],
            'button' => ['required', 'string', 'max:30'],
        ]);

        $this->settings->setMany([
            'home_newsletter_title' => $data['title'],
            'home_newsletter_text' => $data['text'],
            'home_newsletter_placeholder' => $data['placeholder'],
            'home_newsletter_button' => $data['button'],
        ], 'home');
        ActivityLog::record('settings.home_newsletter.updated');

        return back()->with('status', 'Newsletter section updated successfully.');
    }

    public function startSelling(): View
    {
        return view('admin.settings.start-selling', [
            'content' => HomeContent::startSelling(),
        ]);
    }

    public function updateStartSelling(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'text' => ['required', 'string', 'max:300'],
            'primary_label' => ['required', 'string', 'max:40'],
            'primary_url' => ['nullable', 'string', 'max:500'],
            'secondary_label' => ['required', 'string', 'max:40'],
            'secondary_url' => ['nullable', 'string', 'max:500'],
            'app_heading' => ['required', 'string', 'max:80'],
            'app_text' => ['required', 'string', 'max:160'],
            'qr_caption' => ['required', 'string', 'max:60'],
            'title_size' => ['nullable', 'integer', 'between:20,120'],
            'text_size' => ['nullable', 'integer', 'between:10,40'],
            'qr_url' => ['nullable', 'string', 'max:500'],
            'ios_url' => ['nullable', 'url', 'max:500'],
            'android_url' => ['nullable', 'url', 'max:500'],
            'phone_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'qr_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'qr_generated' => ['nullable', 'string', 'max:200000', 'starts_with:data:image/png;base64,'],
            'remove_phone_image' => ['nullable', 'boolean'],
            'remove_qr_image' => ['nullable', 'boolean'],
        ]);

        $values = [];
        foreach (['title', 'text', 'primary_label', 'primary_url', 'secondary_label', 'secondary_url', 'app_heading', 'app_text', 'qr_caption', 'qr_url', 'ios_url', 'android_url', 'title_size', 'text_size'] as $key) {
            $values["home_sell_{$key}"] = $data[$key] ?? '';
        }

        $values['home_sell_phone_image'] = $this->resolveImage(
            $request->file('phone_image'),
            $request->boolean('remove_phone_image'),
            $this->settings->get('home_sell_phone_image'),
        );

        $currentQr = $this->settings->get('home_sell_qr_image');
        $generatedQr = $request->hasFile('qr_image') ? null : $this->storeGeneratedQr($data['qr_generated'] ?? null);
        $values['home_sell_qr_image'] = $generatedQr
            ? $this->replaceStoredImage($currentQr, $generatedQr)
            : $this->resolveImage($request->file('qr_image'), $request->boolean('remove_qr_image'), $currentQr);

        $this->settings->setMany($values, 'home');
        ActivityLog::record('settings.home_start_selling.updated');

        return back()->with('status', 'Start Selling section updated successfully.');
    }

    private function resolveImage(?UploadedFile $file, bool $remove, ?string $currentPath): ?string
    {
        if ($file && $file->isValid()) {
            return $this->replaceStoredImage($currentPath, FileUploadHelper::store($file, 'branding', 'public'));
        }

        return $remove ? $this->replaceStoredImage($currentPath, null) : $currentPath;
    }

    private function replaceStoredImage(?string $oldPath, ?string $newPath): ?string
    {
        if (filled($oldPath) && $oldPath !== $newPath && ! str_starts_with($oldPath, 'http') && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $newPath;
    }

    private function storeGeneratedQr(?string $dataUrl): ?string
    {
        if (blank($dataUrl)) {
            return null;
        }

        $binary = base64_decode(Str::after($dataUrl, 'base64,'), strict: true);
        if ($binary === false || ! str_starts_with($binary, "\x89PNG")) {
            return null;
        }

        $path = 'branding/'.Str::random(40).'.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
