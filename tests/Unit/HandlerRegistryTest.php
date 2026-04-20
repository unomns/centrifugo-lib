<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use Unomns\Centrifugo\AbstractChannelHandler;
use Unomns\Centrifugo\CentrifugoManager;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;
use Unomns\Centrifugo\Exceptions\HandlerNotFoundException;
use Unomns\Centrifugo\HandlerRegistry;
use Unomns\Centrifugo\Response\SubscribeResult;

class HandlerRegistryTest extends TestCase
{
    private CentrifugoManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = Mockery::mock(CentrifugoManager::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_has_returns_false_for_unknown_namespace(): void
    {
        $registry = new HandlerRegistry([], $this->manager);

        $this->assertFalse($registry->has('chat'));
    }

    public function test_has_returns_true_after_register(): void
    {
        $registry = new HandlerRegistry([], $this->manager);
        $registry->register('chat', StubHandler::class);

        $this->assertTrue($registry->has('chat'));
    }

    public function test_resolve_throws_for_unknown_namespace(): void
    {
        $registry = new HandlerRegistry([], $this->manager);

        $this->expectException(HandlerNotFoundException::class);
        $registry->resolve('chat');
    }

    public function test_all_returns_registered_map(): void
    {
        $registry = new HandlerRegistry(['chat' => StubHandler::class], $this->manager);

        $this->assertSame(['chat' => StubHandler::class], $registry->all());
    }

    public function test_programmatic_registration_overrides_initial_map(): void
    {
        $registry = new HandlerRegistry(['chat' => StubHandler::class], $this->manager);
        $registry->register('chat', AnotherStubHandler::class);

        $this->assertSame(AnotherStubHandler::class, $registry->all()['chat']);
    }
}

class StubHandler extends AbstractChannelHandler
{
    public function subscribe(SubscribeRequestDto $dto): SubscribeResult
    {
        return SubscribeResult::allow();
    }
}

class AnotherStubHandler extends AbstractChannelHandler
{
    public function subscribe(SubscribeRequestDto $dto): SubscribeResult
    {
        return SubscribeResult::allow();
    }
}
