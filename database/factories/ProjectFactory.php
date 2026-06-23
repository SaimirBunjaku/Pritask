<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->catchPhrase(),
            'description' => fake()->sentence(12),
            'start_date' => $start,
            'deadline' => fake()->dateTimeBetween($start, '+3 months'),
        ];
    }
}
