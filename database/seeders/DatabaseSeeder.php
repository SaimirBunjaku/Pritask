<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $tags = collect([
            ['name' => 'bug', 'color' => '#e11d48'],
            ['name' => 'feature', 'color' => '#2563eb'],
            ['name' => 'enhancement', 'color' => '#7c3aed'],
            ['name' => 'documentation', 'color' => '#0891b2'],
            ['name' => 'question', 'color' => '#ca8a04'],
            ['name' => 'urgent', 'color' => '#dc2626'],
            ['name' => 'frontend', 'color' => '#16a34a'],
            ['name' => 'backend', 'color' => '#475569'],
        ])->map(fn (array $attributes) => Tag::factory()->create($attributes));

        $projects = [
            Project::factory()->create([
                'name' => 'Website Redesign',
                'description' => 'Revamping the marketing site with a new design system and a faster checkout flow.',
                'start_date' => now()->subWeeks(2),
                'deadline' => now()->addMonths(2),
            ]),
            Project::factory()->create([
                'name' => 'Internal Tools Dashboard',
                'description' => 'Dashboard for the support team to track and resolve customer requests.',
                'start_date' => now()->subMonth(),
                'deadline' => now()->addMonths(3),
            ]),
        ];

        $statusSpread = ['todo', 'todo', 'in_progress', 'in_progress', 'blocked', 'qa_staging', 'qa_done', 'prod', 'prod', 'todo'];

        foreach ($projects as $project) {
            $issues = Issue::factory()
                ->count(10)
                ->for($project)
                ->sequence(...array_map(fn (string $status) => ['status' => $status], $statusSpread))
                ->create();

            foreach ($issues as $issue) {
                $issue->tags()->attach($tags->random(rand(0, 3))->pluck('id'));

                Comment::factory()
                    ->count(rand(0, 3))
                    ->for($issue)
                    ->create();
            }
        }
    }
}
