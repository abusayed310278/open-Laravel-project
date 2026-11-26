<?php

use App\Enums\ProductReportStatus;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;

it('requires authentication to report a product on web', function () {
    $product = Product::factory()->live()->create();

    $this->post(route('products.report', $product), [
        'reason' => 'Counterfeit or fake item',
    ])->assertRedirect(route('login'));
});

it('lets any authenticated user report an existing product via web', function () {
    $product = Product::factory()->live()->create();
    $reporter = User::factory()->create();

    $this->actingAs($reporter)
        ->post(route('products.report', $product), [
            'reason' => 'Misleading title, description, or photos',
            'details' => 'The photos show a different edition.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(ProductReport::count())->toBe(1);
    $report = ProductReport::first();
    expect($report->product_id)->toBe($product->id)
        ->and($report->reporter_id)->toBe($reporter->id)
        ->and($report->reason)->toBe('Misleading title, description, or photos')
        ->and($report->details)->toBe('The photos show a different edition.')
        ->and($report->status)->toBe(ProductReportStatus::Pending);
});

it('lets any authenticated user report a product via API', function () {
    $product = Product::factory()->live()->create();
    $reporter = User::factory()->create();

    $this->actingAs($reporter, 'sanctum')
        ->postJson("/api/products/{$product->id}/report", [
            'reason' => 'Counterfeit or fake item',
            'details' => 'Serial number does not match manufacturer database.',
        ])
        ->assertStatus(201)
        ->assertJsonFragment([
            'message' => 'Thank you for your report. Our team will review this listing.',
        ]);

    expect(ProductReport::where('product_id', $product->id)->count())->toBe(1);
});

it('prevents users from reporting their own product', function () {
    $product = Product::factory()->live()->create();

    $this->actingAs($product->user)
        ->post(route('products.report', $product), [
            'reason' => 'Other violation or issue',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'You cannot report your own product.');

    $this->actingAs($product->user, 'sanctum')
        ->postJson("/api/products/{$product->id}/report", [
            'reason' => 'Other violation or issue',
        ])
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'You cannot report your own product.']);

    expect(ProductReport::count())->toBe(0);
});

it('gracefully handles duplicate pending reports from the same user', function () {
    $product = Product::factory()->live()->create();
    $reporter = User::factory()->create();

    ProductReport::factory()->create([
        'product_id' => $product->id,
        'reporter_id' => $reporter->id,
        'status' => ProductReportStatus::Pending,
    ]);

    $this->actingAs($reporter)
        ->post(route('products.report', $product), [
            'reason' => 'Counterfeit or fake item',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'You have already submitted a report for this product. Our team is reviewing it.');

    $this->actingAs($reporter, 'sanctum')
        ->postJson("/api/products/{$product->id}/report", [
            'reason' => 'Counterfeit or fake item',
        ])
        ->assertStatus(200)
        ->assertJsonFragment([
            'message' => 'You have already submitted a report for this product. Our team is reviewing it.',
        ]);

    expect(ProductReport::count())->toBe(1);
});
