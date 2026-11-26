<?php

namespace Database\Factories;

use App\Enums\ProductReportStatus;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductReportFactory extends Factory
{
    protected $model = ProductReport::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'reporter_id' => User::factory(),
            'reason' => fake()->randomElement([
                'Counterfeit or fake item',
                'Misleading title, description, or photos',
                'Prohibited, illegal, or unsafe item',
                'Defective, damaged, or incorrect condition',
                'Suspected fraud, scam, or price gouging',
                'Other violation or issue',
            ]),
            'details' => fake()->optional()->sentence(),
            'status' => ProductReportStatus::Pending,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => ['status' => ProductReportStatus::Resolved]);
    }

    public function dismissed(): static
    {
        return $this->state(fn () => ['status' => ProductReportStatus::Dismissed]);
    }
}
