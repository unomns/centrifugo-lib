<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Feature;

use Unomns\Centrifugo\Tests\TestCase;

class ValidateCentrifugoSignatureTest extends TestCase
{
    private string $proxyKey = 'test-proxy-key';

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('centrifugo.verify_proxy_signature', true);
        $app['config']->set('centrifugo.proxy_hmac_key', $this->proxyKey);
    }

    public function test_missing_signature_header_returns_401(): void
    {
        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'chat:#1',
            'user'    => '1',
        ])->assertUnauthorized();
    }

    public function test_wrong_signature_returns_401(): void
    {
        $this->withHeaders(['X-Centrifugo-Hmac-Sha256' => 'wrong-signature'])
             ->postJson('/centrifugo/subscribe', [
                 'client'  => 'client-1',
                 'channel' => 'chat:#1',
                 'user'    => '1',
             ])->assertUnauthorized();
    }

    public function test_correct_signature_passes_through(): void
    {
        $data = ['client' => 'client-1', 'channel' => 'unknown:ns', 'user' => '1'];
        $body = json_encode($data);
        $sig  = hash_hmac('sha256', $body, $this->proxyKey);

        $this->withHeaders(['X-Centrifugo-Hmac-Sha256' => $sig])
             ->postJson('/centrifugo/subscribe', $data)
             ->assertOk();
    }

    public function test_verify_false_skips_signature_check(): void
    {
        $this->app['config']->set('centrifugo.verify_proxy_signature', false);

        $this->postJson('/centrifugo/subscribe', [
            'client'  => 'client-1',
            'channel' => 'chat:#1',
            'user'    => '1',
        ])->assertOk();
    }
}
