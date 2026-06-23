<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_update_their_comment(): void
    {
        $user = User::factory()->create(['name' => 'Sam QA']);
        $issue = Issue::factory()->for(Project::factory())->create();
        $comment = Comment::factory()->for($issue)->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'body' => 'Original text',
        ]);

        $response = $this->actingAs($user)->patchJson(
            route('issues.comments.update', [$issue, $comment]),
            ['body' => 'Updated text']
        );

        $response->assertOk();
        $this->assertStringContainsString('Updated text', $response->json('comment'));

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated text',
        ]);
    }

    public function test_other_users_cannot_update_a_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $issue = Issue::factory()->for(Project::factory())->create();
        $comment = Comment::factory()->for($issue)->create([
            'user_id' => $author->id,
            'author_name' => $author->name,
        ]);

        $this->actingAs($other)
            ->patchJson(route('issues.comments.update', [$issue, $comment]), ['body' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_author_can_delete_their_comment(): void
    {
        $user = User::factory()->create();
        $issue = Issue::factory()->for(Project::factory())->create();
        $comment = Comment::factory()->for($issue)->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('issues.comments.destroy', [$issue, $comment]))
            ->assertNoContent();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_other_users_cannot_delete_a_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $issue = Issue::factory()->for(Project::factory())->create();
        $comment = Comment::factory()->for($issue)->create([
            'user_id' => $author->id,
            'author_name' => $author->name,
        ]);

        $this->actingAs($other)
            ->deleteJson(route('issues.comments.destroy', [$issue, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
