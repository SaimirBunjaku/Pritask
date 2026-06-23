<?php

namespace App\Http\Requests;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project' => ['nullable', 'integer', Rule::exists(Project::class, 'id')],
            'status' => ['nullable', 'string', Rule::in(array_keys(Issue::STATUSES))],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
            'tag' => ['nullable', 'integer', Rule::exists(Tag::class, 'id')],
        ];
    }
}
