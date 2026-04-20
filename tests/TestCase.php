<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Unomns\Centrifugo\CentrifugoServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [CentrifugoServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('centrifugo.api_url', 'http://localhost:8000/api');
        $app['config']->set('centrifugo.api_key', 'test-api-key');
        $app['config']->set('centrifugo.secret', 'test-secret');
        $app['config']->set('centrifugo.proxy_hmac_key', 'test-proxy-key');
        $app['config']->set('centrifugo.verify_proxy_signature', false);
        $app['config']->set('centrifugo.token_ttl.auth', 86400);
        $app['config']->set('centrifugo.token_ttl.anon', 7200);
        $app['config']->set('centrifugo.personal_channel_prefix', 'user');
        $app['config']->set('centrifugo.rpc_rate_limit.max', 30);
        $app['config']->set('centrifugo.rpc_rate_limit.window', 60);
        $app['config']->set('centrifugo.handlers', []);
    }
}
