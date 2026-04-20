<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Feature;

use Mockery;
use Unomns\Centrifugo\AbstractChannelHandler;
use Unomns\Centrifugo\CentrifugoManager;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;
use Unomns\Centrifugo\HandlerRegistry;
use Unomns\Centrifugo\Response\DisconnectResponse;
use Unomns\Centrifugo\Response\ErrorResponse;
use Unomns\Centrifugo\Response\SubscribeResult;
use Unomns\Centrifugo\Tests\TestCase;

class SubscribeProxyTest extends TestCase
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

    public function test_unknown_namespace_returns_404_error(): void
    {
        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'unknown:stuff',
            'user'    => '1',
        ])->assertOk()
          ->assertJsonPath('error.code', 404);
    }

    public function test_known_namespace_returns_result(): void
    {
        $manager = $this->manager;
        $this->registerHandler('chat', new class($manager) extends AbstractChannelHandler {
            public function subscribe(SubscribeRequestDto $dto): SubscribeResult
            {
                return SubscribeResult::allow();
            }
        });

        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'chat:#1',
            'user'    => '42',
        ])->assertOk()
          ->assertJsonStructure(['result']);
    }

    public function test_handler_returning_error_propagates_to_response(): void
    {
        $manager = $this->manager;
        $this->registerHandler('chat', new class($manager) extends AbstractChannelHandler {
            public function subscribe(SubscribeRequestDto $dto): ErrorResponse
            {
                return ErrorResponse::withCode(403, 'Unauthorized');
            }
        });

        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'chat:#1',
            'user'    => '1',
        ])->assertOk()
          ->assertJsonPath('error.code', 403);
    }

    public function test_handler_returning_disconnect_propagates_to_response(): void
    {
        $manager = $this->manager;
        $this->registerHandler('chat', new class($manager) extends AbstractChannelHandler {
            public function subscribe(SubscribeRequestDto $dto): DisconnectResponse
            {
                return DisconnectResponse::withCode(4001, 'Banned');
            }
        });

        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'chat:#1',
            'user'    => '1',
        ])->assertOk()
          ->assertJsonPath('disconnect.code', 4001);
    }

    public function test_handler_exception_returns_500_error(): void
    {
        $manager = $this->manager;
        $this->registerHandler('chat', new class($manager) extends AbstractChannelHandler {
            public function subscribe(SubscribeRequestDto $dto): never
            {
                throw new \RuntimeException('Something went wrong');
            }
        });

        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'chat:#1',
            'user'    => '1',
        ])->assertOk()
          ->assertJsonPath('error.code', 500);
    }

    private function registerHandler(string $namespace, AbstractChannelHandler $handler): void
    {
        $registry = Mockery::mock(HandlerRegistry::class);
        $registry->allows('has')->with($namespace)->andReturn(true);
        $registry->allows('has')->andReturn(false);
        $registry->allows('resolve')->with($namespace)->andReturn($handler);

        $this->app->instance(HandlerRegistry::class, $registry);
    }
}
