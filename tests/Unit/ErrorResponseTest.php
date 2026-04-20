<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Unomns\Centrifugo\Response\ErrorResponse;

class ErrorResponseTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $e = new ErrorResponse(403, 'Forbidden');

        $this->assertSame(403, $e->code);
        $this->assertSame('Forbidden', $e->message);
    }

    public function test_with_code_named_constructor(): void
    {
        $e = ErrorResponse::withCode(404, 'Not found');

        $this->assertSame(404, $e->code);
        $this->assertSame('Not found', $e->message);
    }

    public function test_to_array_returns_correct_shape(): void
    {
        $this->assertSame(
            ['code' => 403, 'message' => 'Forbidden'],
            ErrorResponse::withCode(403, 'Forbidden')->toArray(),
        );
    }
}
