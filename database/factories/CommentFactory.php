<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'issue_id' => Issue::factory(),
            'author_name' => fake()->firstName(),
            'body' => fake()->sentence(rand(8, 20)),
        ];
    }
}
