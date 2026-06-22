<?php

namespace Database\Factories;

use App\Models\Issue;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(rand(3, 7)),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => fake()->randomElement(['open', 'open', 'open', 'in_progress', 'in_progress', 'closed']),
            'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high']),
            'due_date' => fake()->optional(0.5)->dateTimeBetween('now', '+2 months'),
        ];
    }
}
