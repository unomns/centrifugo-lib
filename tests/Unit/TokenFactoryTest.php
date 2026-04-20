<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use Unomns\Centrifugo\CentrifugoManager;
use Unomns\Centrifugo\Tests\TestCase;
use Unomns\Centrifugo\TokenFactory;

class TokenFactoryTest extends TestCase
{
    private TokenFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $manager       = new CentrifugoManager('http://localhost:8000/api', 'key', 'test-secret');
        $this->factory = new TokenFactory($manager, authTtl: 86400, anonTtl: 7200);
    }

    public function test_for_user_returns_jwt_string(): void
    {
        $token = $this->factory->forUser(42);

        $this->assertIsString($token);
        $this->assertStringContainsString('.', $token);
    }

    public function test_for_user_authenticated_has_correct_sub_claim(): void
    {
        $token   = $this->factory->forUser(42);
        $payload = $this->decodePayload($token);

        $this->assertSame('42', $payload['sub']);
    }

    public function test_for_user_null_produces_anonymous_token_with_empty_sub(): void
    {
        $token   = $this->factory->forUser(null);
        $payload = $this->decodePayload($token);

        $this->assertSame('', $payload['sub']);
    }

    public function test_for_user_zero_produces_anonymous_token(): void
    {
        $token   = $this->factory->forUser(0);
        $payload = $this->decodePayload($token);

        $this->assertSame('', $payload['sub']);
    }

    public function test_for_user_empty_string_produces_anonymous_token(): void
    {
        $token   = $this->factory->forUser('');
        $payload = $this->decodePayload($token);

        $this->assertSame('', $payload['sub']);
    }

    public function test_anonymous_token_includes_nonce_in_info(): void
    {
        $token   = $this->factory->forUser(null);
        $payload = $this->decodePayload($token);

        $this->assertArrayHasKey('info', $payload);
        $this->assertArrayHasKey('nonce', $payload['info']);
    }

    public function test_for_user_with_client_includes_client_in_info(): void
    {
        $token   = $this->factory->forUser(42, 'client-abc');
        $payload = $this->decodePayload($token);

        $this->assertArrayHasKey('info', $payload);
        $this->assertSame('client-abc', $payload['info']['client']);
    }

    public function test_for_subscription_returns_jwt_with_channel_claim(): void
    {
        $token   = $this->factory->forSubscription(42, 'chat:#1');
        $payload = $this->decodePayload($token);

        $this->assertSame('42', $payload['sub']);
        $this->assertSame('chat:#1', $payload['channel']);
    }

    public function test_for_subscription_respects_ttl_override_in_seconds(): void
    {
        // forSubscription adds time() to the override, so pass a duration (seconds)
        $ttlOverride = 3600;
        $before      = time();
        $token       = $this->factory->forSubscription(42, 'chat:#1', $ttlOverride);
        $after       = time();

        $payload = $this->decodePayload($token);

        $this->assertGreaterThanOrEqual($before + $ttlOverride, $payload['exp']);
        $this->assertLessThanOrEqual($after + $ttlOverride, $payload['exp']);
    }

    private function decodePayload(string $jwt): array
    {
        $parts   = explode('.', $jwt);
        $decoded = base64_decode(strtr($parts[1], '-_', '+/'));

        return json_decode($decoded, true);
    }
}
