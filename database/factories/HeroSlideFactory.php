<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => 'branding/'.$this->faker->unique()->lexify('??????????').'.jpg',
            'product_id' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
