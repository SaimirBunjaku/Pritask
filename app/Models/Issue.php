<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    use HasFactory;

    public const STATUSES = [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'blocked' => 'Blocked',
        'qa_staging' => 'QA Staging',
        'qa_done' => 'QA Done',
        'prod' => 'Prod',
    ];

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function modalData(): array
    {
        $this->loadMissing(['project', 'tags', 'users']);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'statusLabel' => $this->statusLabel(),
            'priority' => $this->priority,
            'dueDate' => $this->due_date?->format('M j, Y'),
            'project' => $this->project->name,
            'projectId' => $this->project_id,
            'tags' => $this->tags->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color ?? '#8e8e93',
            ])->values()->all(),
            'members' => $this->users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])->values()->all(),
            'editUrl' => route('issues.edit', $this),
            'deleteUrl' => route('issues.destroy', $this),
            'showUrl' => route('issues.show', $this),
        ];
    }
}
