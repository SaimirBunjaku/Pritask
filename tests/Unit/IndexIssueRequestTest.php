<?php

namespace Tests\Unit;

use App\Http\Requests\IndexIssueRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexIssueRequestTest extends TestCase
{
    public function test_accepts_valid_filter_params(): void
    {
        $validator = Validator::make(
            ['status' => 'in_progress', 'priority' => 'high'],
            (new IndexIssueRequest)->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_invalid_status_and_priority(): void
    {
        $validator = Validator::make(
            ['status' => 'open', 'priority' => 'urgent'],
            (new IndexIssueRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
        $this->assertArrayHasKey('priority', $validator->errors()->toArray());
    }

    public function test_allows_empty_filters(): void
    {
        $validator = Validator::make([], (new IndexIssueRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_status_rule_matches_issue_statuses(): void
    {
        foreach (array_keys(Issue::STATUSES) as $status) {
            $validator = Validator::make(['status' => $status], (new IndexIssueRequest)->rules());
            $this->assertFalse($validator->fails(), "Expected status [{$status}] to be valid.");
        }
    }
}
