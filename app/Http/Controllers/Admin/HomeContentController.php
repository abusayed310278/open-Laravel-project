<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\SettingsService;
use App\Support\HomeContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
