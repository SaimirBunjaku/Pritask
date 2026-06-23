<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected static array $catalog = [
        'I can reproduce this on staging as well.',
        'Looking into this now, will update once I find the root cause.',
        'This seems related to the change we made last sprint to the auth flow.',
        'Can confirm this is fixed after the latest deploy.',
        'Should we prioritize this before the next release?',
        "I've attached a screenshot showing the issue in the linked PR.",
        'Tested on my end and could not reproduce, can you share more steps?',
        "Moving this to blocked until the API team ships their fix.",
        'Nice catch, this also affects the mobile app.',
        'Adding a regression test so this does not come back.',
        'This looks like a duplicate of an issue we closed last month.',
        'Pushed a fix, ready for QA.',
        'Verified in staging, looks good to me.',
        'We will need design input before implementing this.',
        'Bumping priority since this is affecting multiple customers now.',
    ];

    public function definition(): array
    {
        return [
            'issue_id' => Issue::factory(),
            'user_id' => User::factory(),
            'author_name' => fake()->firstName(),
            'body' => fake()->randomElement(self::$catalog),
        ];
    }
}
