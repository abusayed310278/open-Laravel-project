<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function startSellingPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Sell fast',
        'text' => 'Custom description',
        'primary_label' => 'Join Now',
        'primary_url' => '/custom-register',
        'secondary_label' => 'Browse',
        'secondary_url' => 'https://example.com/deals',
        'app_heading' => 'Get our app',
        'app_text' => 'Faster on mobile',
        'qr_caption' => 'Scan me',
    ], $overrides);
}

it('saves editable copy, links, images and store links and renders them on the home page', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.settings.start-selling.update'), startSellingPayloadWithMedia())
        ->assertRedirect();

    $page = $this->get(route('home'))->assertOk();

    $page->assertSee('Sell fast')
        ->assertSee('Join Now')
        ->assertSee(url('/custom-register'), false)
        ->assertSee('https://example.com/deals', false)
        ->assertSee('Get our app')
        ->assertSee('https://apps.apple.com/app/id1', false)
        ->assertSee('https://play.google.com/store/apps/details?id=x', false)
        ->assertSee('Scan me');

    expect(Storage::disk('public')->allFiles('branding'))->toHaveCount(2);
});

it('stores a generated QR png and rejects non-png data', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $png = 'data:image/png;base64,'.base64_encode("\x89PNG\r\n\x1a\nfake");

    $this->actingAs($admin)
        ->post(route('admin.settings.start-selling.update'), startSellingPayload(['qr_generated' => $png]))
        ->assertRedirect();

    expect(Storage::disk('public')->allFiles('branding'))->toHaveCount(1);

    $this->actingAs($admin)
        ->post(route('admin.settings.start-selling.update'), startSellingPayload(['qr_generated' => 'data:image/png;base64,'.base64_encode('not a png')]))
        ->assertRedirect();

    expect(Storage::disk('public')->allFiles('branding'))->toHaveCount(1);
});

it('falls back to the simple centered banner when no app media is set', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Start selling your electronics')
        ->assertDontSee('Shop and sell on the go');
});

function startSellingPayloadWithMedia(): array
{
    return startSellingPayload([
        'ios_url' => 'https://apps.apple.com/app/id1',
        'android_url' => 'https://play.google.com/store/apps/details?id=x',
        'phone_image' => UploadedFile::fake()->image('phone.png', 600, 1200),
        'qr_image' => UploadedFile::fake()->image('qr.png', 300, 300),
    ]);
}
