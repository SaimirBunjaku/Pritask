<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IssueAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_user_sends_them_a_notification(): void
    {
        Notification::fake();

        $assigner = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $assigner->id]);
        $issue = Issue::factory()->for($project)->create();

        $this->actingAs($assigner)
            ->post(route('issues.users.attach', [$issue, $assignee]))
            ->assertOk();

        Notification::assertSentTo($assignee, IssueAssignedNotification::class);
    }

    public function test_self_assignment_does_not_send_a_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $issue = Issue::factory()->for($project)->create();

        $this->actingAs($user)
            ->post(route('issues.users.attach', [$issue, $user]))
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_notifications_index_returns_unread_assignments(): void
    {
        $assigner = User::factory()->create(['name' => 'Jordan Dev']);
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $assigner->id]);
        $issue = Issue::factory()->for($project)->create(['title' => 'Fix checkout bug']);

        $this->actingAs($assigner)
            ->post(route('issues.users.attach', [$issue, $assignee]))
            ->assertOk();

        $response = $this->actingAs($assignee)->getJson(route('notifications.index'));

        $response->assertOk()
            ->assertJsonPath('unreadCount', 1)
            ->assertJsonPath('notifications.0.issue_url', route('issues.show', $issue, absolute: false))
            ->assertJsonFragment([
                'message' => 'Jordan Dev assigned you to "Fix checkout bug"',
            ]);
    }
}
