<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('issue')) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
        ];
    }
}
