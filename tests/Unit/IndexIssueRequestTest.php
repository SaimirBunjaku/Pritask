<?php

namespace Tests\Unit;

use App\Http\Requests\IndexIssueRequest;
use App\Models\Issue;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexIssueRequestTest extends TestCase
{
    public function test_valid_board_filters_pass_validation(): void
    {
        $request = new IndexIssueRequest();
        $validator = Validator::make([
            'status' => 'in_progress',
            'priority' => 'high',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_status_is_rejected(): void
    {
        $request = new IndexIssueRequest();
        $validator = Validator::make([
            'status' => 'open',
        ], $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_kanban_statuses_are_allowed(): void
    {
        $request = new IndexIssueRequest();

        foreach (array_keys(Issue::STATUSES) as $status) {
            $validator = Validator::make(['status' => $status], $request->rules());
            $this->assertFalse($validator->fails(), "Status [{$status}] should be valid.");
        }
    }
}
