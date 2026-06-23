<?php

namespace App\Http\Requests;

use App\Models\Issue;
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
            'project' => ['nullable', 'integer', 'exists:projects,id'],
            'status' => ['nullable', Rule::in(array_keys(Issue::STATUSES))],
            'priority' => ['nullable', 'in:low,medium,high'],
            'tag' => ['nullable', 'integer', 'exists:tags,id'],
        ];
    }
}
