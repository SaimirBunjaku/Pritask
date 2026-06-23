<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Issue $issue,
        public User $assignedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'issue_title' => $this->issue->title,
            'assigned_by' => $this->assignedBy->name,
            'issue_url' => route('issues.show', $this->issue, absolute: false),
        ];
    }
}
