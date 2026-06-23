<?php

namespace Tests\Unit;

use App\Http\Requests\StoreCommentRequest;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class StoreCommentRequestTest extends TestCase
{
    public function test_requires_body_only(): void
    {
        $validator = Validator::make([], (new StoreCommentRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('body', $validator->errors()->toArray());
        $this->assertArrayNotHasKey('author_name', $validator->errors()->toArray());
    }

    public function test_accepts_body_without_author_name(): void
    {
        $validator = Validator::make(
            ['body' => 'Looks good to me.'],
            (new StoreCommentRequest)->rules()
        );

        $this->assertFalse($validator->fails());
    }
}
