<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Unomns\Centrifugo\Response\OverrideOptions;
use Unomns\Centrifugo\Response\SubscribeResult;

class SubscribeResultTest extends TestCase
{
    public function test_allow_produces_empty_array(): void
    {
        $result = SubscribeResult::allow();

        $this->assertSame([], $result->toArray());
    }

    public function test_allow_with_override(): void
    {
        $override = new OverrideOptions(presence: true);
        $result   = SubscribeResult::allow($override);

        $this->assertSame(
            ['override' => ['presence' => true]],
            $result->toArray(),
        );
    }

    public function test_allow_with_token(): void
    {
        $result = SubscribeResult::allowWithToken('my-token');

        $this->assertSame(['token' => 'my-token'], $result->toArray());
    }

    public function test_allow_with_token_and_override(): void
    {
        $override = new OverrideOptions(presence: true);
        $result   = SubscribeResult::allowWithToken('my-token', $override);

        $array = $result->toArray();
        $this->assertSame('my-token', $array['token']);
        $this->assertSame(['presence' => true], $array['override']);
    }

    public function test_allow_with_override_named_constructor(): void
    {
        $override = new OverrideOptions(join_leave: true);
        $result   = SubscribeResult::allowWithOverride($override);

        $this->assertSame(['override' => ['join_leave' => true]], $result->toArray());
    }

    public function test_to_array_omits_null_fields(): void
    {
        $result = new SubscribeResult(info: ['key' => 'val']);

        $array = $result->toArray();
        $this->assertArrayHasKey('info', $array);
        $this->assertArrayNotHasKey('b64info', $array);
        $this->assertArrayNotHasKey('token', $array);
    }
}
