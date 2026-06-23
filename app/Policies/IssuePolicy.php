<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function view(?User $user, Issue $issue): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Issue $issue): bool
    {
        return $user !== null;
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $user !== null;
    }

    public function assignMembers(User $user, Issue $issue): bool
    {
        return $user !== null;
    }
}
