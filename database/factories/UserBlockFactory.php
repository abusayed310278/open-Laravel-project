<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBlockFactory extends Factory
{
    protected $model = UserBlock::class;

    public function definition(): array
    {
        return [
            'blocker_id' => User::factory(),
            'blocked_id' => User::factory(),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
