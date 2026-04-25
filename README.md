# centrifugo-laravel

Laravel package for integrating [Centrifugo](https://centrifugal.dev/) HTTP proxy — handles connect, subscribe, publish, RPC, refresh, and sub-refresh proxy requests via a handler-based architecture.

## Requirements

- PHP ^8.2
- Laravel 10, 11, or 12

## Installation

```bash
composer require unomns/centrifugo-laravel
```

```bash
php artisan vendor:publish --provider="Unomns\Centrifugo\CentrifugoServiceProvider" --tag=centrifugo-config
```

Optionally publish the routes to customise prefix or middleware:

```bash
php artisan vendor:publish --provider="Unomns\Centrifugo\CentrifugoServiceProvider" --tag=centrifugo-routes
```

## Configuration

```env
CENTRIFUGO_API_URL=http://localhost:8000/api
CENTRIFUGO_API_KEY=your-api-key
CENTRIFUGO_HMAC_SECRET_KEY=your-secret
CENTRIFUGO_PROXY_HMAC_KEY=your-proxy-key
```

| Key | Env | Default | Description |
|---|---|---|---|
| `api_url` | `CENTRIFUGO_API_URL` | `http://localhost:8000/api` | Centrifugo HTTP API endpoint |
| `api_key` | `CENTRIFUGO_API_KEY` | — | Centrifugo API key |
| `secret` | `CENTRIFUGO_HMAC_SECRET_KEY` | — | HMAC secret for JWT signing |
| `verify_proxy_signature` | `CENTRIFUGO_VERIFY_PROXY_SIGNATURE` | `true` | Validate `X-Centrifugo-Hmac-Sha256` on proxy requests |
| `proxy_hmac_key` | `CENTRIFUGO_PROXY_HMAC_KEY` | — | Key used to verify proxy request signatures |
| `token_ttl.auth` | `CENTRIFUGO_AUTH_TOKEN_TTL` | `86400` | Authenticated token lifetime (seconds) |
| `token_ttl.anon` | `CENTRIFUGO_ANON_TOKEN_TTL` | `7200` | Anonymous token lifetime (seconds) |
| `personal_channel_prefix` | `CENTRIFUGO_PERSONAL_PREFIX` | `user` | Prefix for personal channels |
| `rpc_rate_limit.max` | `CENTRIFUGO_RPC_RATE_LIMIT_MAX` | `30` | Max RPC calls per window |
| `rpc_rate_limit.window` | `CENTRIFUGO_RPC_RATE_LIMIT_WINDOW` | `60` | Rate limit window (seconds) |
| `handlers` | — | `[]` | Namespace → handler class map |

## Proxy routes

The package registers these routes automatically:

```
POST /centrifugo/connect
POST /centrifugo/subscribe
POST /centrifugo/publish
POST /centrifugo/rpc
POST /centrifugo/refresh
POST /centrifugo/sub-refresh
```

All routes are protected by `ValidateCentrifugoSignature` middleware, which validates the `X-Centrifugo-Hmac-Sha256` header. Set `CENTRIFUGO_VERIFY_PROXY_SIGNATURE=false` in local dev if Centrifugo isn't configured to sign requests.

`refresh` and `sub-refresh` have default implementations that re-issue tokens. Override them if you need revocation logic.

## Connect

The connect endpoint is a stub (returns HTTP 400) — you must implement it since authentication is application-specific. Publish the routes file and replace the action.

Token-proxy mode — Centrifugo validates the JWT, extracts the user:

```php
use Unomns\Centrifugo\Http\Requests\ConnectProxyRequest;
use Unomns\Centrifugo\Response\ConnectProxyResponse;
use Unomns\Centrifugo\Response\ConnectResult;
use Unomns\Centrifugo\TokenFactory;

public function connect(ConnectProxyRequest $request): JsonResponse
{
    $token = app(TokenFactory::class)->forUser(
        userId: auth()->id(),
        client: $request->clientId(),
    );

    return response()->json(ConnectProxyResponse::withResult(
        ConnectResult::withToken($token)
    ));
}
```

Proxy-based auth — your backend is the authority, no JWT needed:

```php
public function connect(ConnectProxyRequest $request): JsonResponse
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(ConnectProxyResponse::withDisconnect(
            new DisconnectResponse(4000, 'Unauthorized')
        ));
    }

    return response()->json(ConnectProxyResponse::withResult(
        ConnectResult::withUser((string) $user->id)
    ));
}
```

## Channel handlers

Extend `AbstractChannelHandler` and implement the methods your channel needs:

```php
use Unomns\Centrifugo\AbstractChannelHandler;
use Unomns\Centrifugo\Dto\PublishRequestDto;
use Unomns\Centrifugo\Dto\RpcRequestDto;
use Unomns\Centrifugo\Dto\RpcResponseDto;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;
use Unomns\Centrifugo\Response\DisconnectResponse;
use Unomns\Centrifugo\Response\ErrorResponse;
use Unomns\Centrifugo\Response\PublishResult;
use Unomns\Centrifugo\Response\SubscribeResult;

class ChatHandler extends AbstractChannelHandler
{
    public function subscribe(SubscribeRequestDto $dto): SubscribeResult|ErrorResponse|DisconnectResponse
    {
        if (!auth()->check()) {
            return ErrorResponse::withCode(403, 'Unauthorized');
        }

        return SubscribeResult::allow();
    }

    public function publish(PublishRequestDto $dto): PublishResult|ErrorResponse|DisconnectResponse
    {
        // called only when publish proxy is enabled for this channel in Centrifugo config
        if (!auth()->check()) {
            return ErrorResponse::withCode(403, 'Unauthorized');
        }

        return PublishResult::allow();
    }

    public function rpc(RpcRequestDto $dto): RpcResponseDto
    {
        return match ($dto->rpcMethod) {
            'sendMessage' => new RpcResponseDto(data: $this->handleSendMessage($dto)),
            default       => parent::rpc($dto), // returns structured 405
        };
    }
}
```

Register in `config/centrifugo.php`:

```php
'handlers' => [
    'chat' => \App\Websocket\ChatHandler::class,
],
```

Or programmatically in a service provider:

```php
use Unomns\Centrifugo\CentrifugoServiceProvider;

CentrifugoServiceProvider::registerHandler('chat', ChatHandler::class);
```

## Publishing messages

Extend `AbstractChannelPusher` to push messages from your application:

```php
use Unomns\Centrifugo\AbstractChannelPusher;

class ChatPusher extends AbstractChannelPusher
{
    public function namespace(): ?string { return 'chat'; }

    public function sendMessage(int $roomId, array $message): void
    {
        $this->publish("chat:#{$roomId}", 'new_message', $message);
    }
}
```

Messages are published as `['type' => $type, 'payload' => $payload]`.

```php
$pusher->publishToUser(userId: $user->id, type: 'notification', payload: $data);
// → "user:#42"

$pusher->broadcast(['chat:#1', 'chat:#2'], type: 'alert', payload: $data);

$pusher->broadcastToUsers([1, 2, 3], type: 'notification', payload: $data);
// → "user:#1", "user:#2", "user:#3"
```

## Channel naming

```php
use Unomns\Centrifugo\ChannelHelper;

ChannelHelper::general('news');          // "news"
ChannelHelper::typed('news', 'sports');  // "news:sports"
ChannelHelper::dynamic('chat', 42);      // "chat:#42"
ChannelHelper::personal(42);             // "user:#42"
```

## Subscription tokens

For token-based channel auth instead of subscribe proxy:

```php
$token = $tokens->forSubscription($user->id, 'chat:#42');
```

## License

MIT
