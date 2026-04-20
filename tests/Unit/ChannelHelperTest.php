<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Unomns\Centrifugo\ChannelHelper;

class ChannelHelperTest extends TestCase
{
    public function test_general_returns_namespace_as_is(): void
    {
        $this->assertSame('news', ChannelHelper::general('news'));
    }

    public function test_typed_combines_namespace_and_type(): void
    {
        $this->assertSame('news:sports', ChannelHelper::typed('news', 'sports'));
    }

    public function test_dynamic_combines_namespace_and_id_with_hash(): void
    {
        $this->assertSame('chat:#42', ChannelHelper::dynamic('chat', 42));
        $this->assertSame('chat:#abc', ChannelHelper::dynamic('chat', 'abc'));
    }

    public function test_personal_uses_default_user_prefix(): void
    {
        $this->assertSame('user:#7', ChannelHelper::personal(7));
    }

    public function test_personal_accepts_custom_prefix(): void
    {
        $this->assertSame('member:#7', ChannelHelper::personal(7, 'member'));
    }
}
