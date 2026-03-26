<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Unomns\Centrifugo\Http\Middleware\ValidateCentrifugoSignature;

class CentrifugoServiceProvider extends ServiceProvider
{
    /**
     * Handlers registered programmatically before register() runs.
     * Populated via registerHandler(); merged with config at singleton build time.
     *
     * @var array<string, string>
     */
    private static array $handlerMap = [];

    /**
     * Register a namespace → handler class binding programmatically.
     *
     * Call from your application's service provider boot() phase:
     *
     *   CentrifugoServiceProvider::registerHandler('chat', ChatHandler::class);
     *
     * Programmatic registrations override config-declared handlers on conflict.
     */
    public static function registerHandler(string $namespace, string $handlerClass): void
    {
        static::$handlerMap[$namespace] = $handlerClass;
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/centrifugo.php', 'centrifugo');

        $this->app->singleton(CentrifugoManager::class, function ($app): CentrifugoManager {
            $cfg = $app['config']['centrifugo'];

            return new CentrifugoManager(
                apiUrl: $cfg['api_url'],
                apiKey: $cfg['api_key'],
                secret: $cfg['secret'],
            );
        });

        $this->app->singleton(TokenFactory::class, function ($app): TokenFactory {
            $cfg = $app['config']['centrifugo'];

            return new TokenFactory(
                manager: $app->make(CentrifugoManager::class),
                authTtl: (int) $cfg['token_ttl']['auth'],
                anonTtl: (int) $cfg['token_ttl']['anon'],
            );
        });

        $this->app->singleton(HandlerRegistry::class, function ($app): HandlerRegistry {
            $configHandlers = (array) ($app['config']['centrifugo']['handlers'] ?? []);

            // Programmatic registrations win on conflict.
            $merged = array_merge($configHandlers, static::$handlerMap);

            return new HandlerRegistry($merged, $app->make(CentrifugoManager::class));
        });

        $this->app->alias(CentrifugoManager::class, 'centrifugo');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/centrifugo.php' => config_path('centrifugo.php'),
            ], 'centrifugo-config');

            $this->publishes([
                __DIR__ . '/../routes/centrifugo.php' => base_path('routes/centrifugo.php'),
            ], 'centrifugo-routes');
        }

        $this->app->make(Router::class)->aliasMiddleware(
            'centrifugo.signature',
            ValidateCentrifugoSignature::class,
        );

        $this->loadRoutes();
    }

    private function loadRoutes(): void
    {
        // Once the developer publishes the routes file they own it — skip
        // auto-registration so their customisations (middleware, prefix…) take effect.
        if (file_exists(base_path('routes/centrifugo.php'))) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/centrifugo.php');
    }
}
