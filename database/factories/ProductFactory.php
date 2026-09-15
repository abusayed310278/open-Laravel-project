<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst($this->faker->words(3, true));
        return [
            'user_id' => User::factory()->role(UserRole::Business),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . uniqid(),
            'description' => $this->faker->paragraph(),
            'condition' => 'new',
            'grade' => 'A',
            'status' => 'draft',
            'approval_status' => 'pending',
            'publication_status' => 'unpublished',
            'payment_route' => 'seller',
            'price' => $this->faker->randomFloat(2, 20, 2000),
            'sku' => strtoupper($this->faker->bothify('SKU-####??')),
            'quantity' => 10,
            'shipping_type' => 'free',
        ];
    }

    /**
     * Approved, published, and visible on the storefront.
     */
    public function live(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'approval_status' => 'approved',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);
    }
}
