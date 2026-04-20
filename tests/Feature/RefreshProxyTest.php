<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Feature;

use Unomns\Centrifugo\Tests\TestCase;

class RefreshProxyTest extends TestCase
{
    public function test_refresh_returns_token_for_authenticated_user(): void
    {
        $this->postJson('/centrifugo/refresh', [
            'client' => 'client-1',
            'user'   => '42',
        ])->assertOk()
          ->assertJsonStructure(['result' => ['token']]);
    }

    public function test_refresh_returns_disconnect_for_anonymous_user(): void
    {
        $this->postJson('/centrifugo/refresh', [
            'client' => 'client-1',
            'user'   => '',
        ])->assertOk()
          ->assertJsonPath('disconnect.code', 4000);
    }

    public function test_refresh_requires_client_field(): void
    {
        $this->postJson('/centrifugo/refresh', [
            'user' => '42',
        ])->assertUnprocessable();
    }
}
