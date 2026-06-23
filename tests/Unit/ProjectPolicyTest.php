<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    public function test_owner_can_update_and_delete_project(): void
    {
        $owner = new User;
        $owner->id = 1;
        $project = new Project(['user_id' => 1]);
        $policy = new ProjectPolicy;

        $this->assertTrue($policy->update($owner, $project));
        $this->assertTrue($policy->delete($owner, $project));
    }

    public function test_non_owner_cannot_update_or_delete_project(): void
    {
        $owner = new User;
        $owner->id = 1;
        $other = new User;
        $other->id = 2;
        $project = new Project(['user_id' => 1]);
        $policy = new ProjectPolicy;

        $this->assertFalse($policy->update($other, $project));
        $this->assertFalse($policy->delete($other, $project));
    }
}
