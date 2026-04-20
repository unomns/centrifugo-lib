<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Unomns\Centrifugo\Response\DisconnectResponse;

class DisconnectResponseTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $d = new DisconnectResponse(4000, 'Unauthorized');

        $this->assertSame(4000, $d->code);
        $this->assertSame('Unauthorized', $d->reason);
    }

    public function test_with_code_named_constructor(): void
    {
        $d = DisconnectResponse::withCode(4001, 'Banned');

        $this->assertSame(4001, $d->code);
        $this->assertSame('Banned', $d->reason);
    }

    public function test_to_array_returns_correct_shape(): void
    {
        $this->assertSame(
            ['code' => 4001, 'reason' => 'Banned'],
            DisconnectResponse::withCode(4001, 'Banned')->toArray(),
        );
    }
}
