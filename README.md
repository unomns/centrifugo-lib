# centrifugo-laravel

A Laravel package that integrates [Centrifugo](https://centrifugal.dev/) real-time WebSocket server with Laravel applications. It handles Centrifugo proxy requests (connect, subscribe, RPC) via a handler-based architecture.

## Requirements

- PHP ^8.2
- Laravel 10, 11, or 12

## Installation

```bash
composer require unomns/centrifugo-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --provider="Unomns\Centrifugo\CentrifugoServiceProvider" --tag=config
```

Optionally publish the routes (if you want to customise them):

```bash
php artisan vendor:publish --provider="Unomns\Centrifugo\CentrifugoServiceProvider" --tag=routes
```

## Configuration

Add the following variables to your `.env`:

```env
CENTRIFUGO_API_URL=http://localhost:8000/api
CENTRIFUGO_API_KEY=your-api-key
CENTRIFUGO_HMAC_SECRET_KEY=your-secret
CENTRIFUGO_PROXY_HMAC_KEY=your-proxy-key
```

Full options in `config/centrifugo.php`:

| Key | Env variable | Default | Description |
|---|---|---|---|
| `api_url` | `CENTRIFUGO_API_URL` | `http://localhost:8000/api` | Centrifugo HTTP API endpoint |
| `api_key` | `CENTRIFUGO_API_KEY` | — | Centrifugo API key |
| `secret` | `CENTRIFUGO_HMAC_SECRET_KEY` | — | HMAC secret for JWT signing |
| `verify_proxy_signature` | `CENTRIFUGO_VERIFY_PROXY_SIGNATURE` | `true` | Validate `X-Centrifugo-Hmac-Sha256` header on proxy requests |
| `proxy_hmac_key` | `CENTRIFUGO_PROXY_HMAC_KEY` | — | Key used to verify proxy request signatures (separate from `secret`) |
| `token_ttl.auth` | `CENTRIFUGO_AUTH_TOKEN_TTL` | `86400` | Authenticated token lifetime (seconds) |
| `token_ttl.anon` | `CENTRIFUGO_ANON_TOKEN_TTL` | `7200` | Anonymous token lifetime (seconds) |
| `personal_channel_prefix` | `CENTRIFUGO_PERSONAL_PREFIX` | `user` | Prefix for personal channels (`user:#1`) |
| `rpc_rate_limit.max` | `CENTRIFUGO_RPC_RATE_LIMIT_MAX` | `30` | Max RPC calls per window |
| `rpc_rate_limit.window` | `CENTRIFUGO_RPC_RATE_LIMIT_WINDOW` | `60` | Rate limit window (seconds) |
| `handlers` | — | `[]` | Namespace → handler class map |

## Usage

### Proxy routes

The package registers five proxy routes automatically:

```
POST /centrifugo/connect
POST /centrifugo/subscribe
POST /centrifugo/rpc
POST /centrifugo/refresh
POST /centrifugo/sub-refresh
```

Configure these URLs in Centrifugo's `proxy` settings.

All routes are protected by the `ValidateCentrifugoSignature` middleware, which validates the `X-Centrifugo-Hmac-Sha256` header Centrifugo attaches to every proxy request. Set `CENTRIFUGO_VERIFY_PROXY_SIGNATURE=false` in local dev if your Centrifugo instance isn't configured to sign requests.

The `refresh` and `sub-refresh` endpoints have working default implementations that re-issue tokens. Publish the routes file and replace the actions if you need revocation checks:

```bash
php artisan vendor:publish --provider="Unomns\Centrifugo\CentrifugoServiceProvider" --tag=centrifugo-routes
```

### Connection tokens

Use the `TokenFactory` (or the `Centrifugo` facade) to issue connection tokens in your connect endpoint:

```php
use Unomns\Centrifugo\TokenFactory;

class ConnectController extends Controller
{
    public function __invoke(Request $request, TokenFactory $tokens)
    {
        return response()->json([
            'result' => [
                'token' => $tokens->forUser($request->user()?->id),
            ],
        ]);
    }
}
```

Pass `null` / `0` / `''` to issue an anonymous token. Pass the `client` ID from the proxy request to bind the token to the specific connection.

### Channel handlers

Create a handler for each Centrifugo channel namespace:

```php
use Unomns\Centrifugo\AbstractChannelHandler;
use Unomns\Centrifugo\Dto\RpcRequestDto;
use Unomns\Centrifugo\Dto\RpcResponseDto;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;
use Unomns\Centrifugo\Response\DisconnectResponse;
use Unomns\Centrifugo\Response\ErrorResponse;
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

    public function rpc(RpcRequestDto $dto): RpcResponseDto
    {
        return match ($dto->rpcMethod) {
            'sendMessage' => new RpcResponseDto(data: $this->handleSendMessage($dto)),
            default       => parent::rpc($dto), // returns structured 405
        };
    }
}
```

Register handlers in `config/centrifugo.php`:

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

### Publishing messages

Extend `AbstractChannelPusher` to push messages from your application:

```php
use Unomns\Centrifugo\AbstractChannelPusher;

class ChatPusher extends AbstractChannelPusher
{
    public function namespace(): ?string { return 'chat'; }

    public function sendMessage(int $roomId, array $message): void
    {
        $this->publish(
            channel: "chat:#{$roomId}",
            type: 'new_message',
            payload: $message,
        );
    }
}
```

All messages are published as `['type' => $type, 'payload' => $payload]` so clients can dispatch on `type`.

To publish to a user's personal channel:

```php
$pusher->publishToUser(userId: $user->id, type: 'notification', payload: $data);
// publishes to "user:#42"
```

To broadcast to multiple channels or users at once:

```php
$pusher->broadcast(['chat:#1', 'chat:#2'], type: 'alert', payload: $data);

$pusher->broadcastToUsers([1, 2, 3], type: 'notification', payload: $data);
// publishes to "user:#1", "user:#2", "user:#3"
```

### Channel naming helpers

```php
use Unomns\Centrifugo\ChannelHelper;

ChannelHelper::general('news');          // "news"
ChannelHelper::typed('news', 'sports');  // "news:sports"
ChannelHelper::dynamic('chat', 42);      // "chat:#42"
ChannelHelper::personal(42);             // "user:#42"
```

### Subscription tokens

For token-based channel auth (instead of proxy subscribe):

```php
$token = $tokens->forSubscription($user->id, 'chat:#42');
```

## License

MIT
