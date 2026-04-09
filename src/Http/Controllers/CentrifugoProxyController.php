<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Unomns\Centrifugo\Dto\RpcResponseDto;
use Unomns\Centrifugo\HandlerRegistry;
use Unomns\Centrifugo\Http\Requests\ConnectProxyRequest;
use Unomns\Centrifugo\Http\Requests\RefreshProxyRequest;
use Unomns\Centrifugo\Http\Requests\RpcProxyRequest;
use Unomns\Centrifugo\Http\Requests\SubRefreshProxyRequest;
use Unomns\Centrifugo\Http\Requests\SubscribeProxyRequest;
use Unomns\Centrifugo\TokenFactory;
use Unomns\Centrifugo\Response\DisconnectResponse;
use Unomns\Centrifugo\Response\ErrorResponse;
use Unomns\Centrifugo\Response\RpcProxyResponse;
use Unomns\Centrifugo\Response\SubscribeProxyResponse;
use Unomns\Centrifugo\Response\SubscribeResult;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CentrifugoProxyController extends Controller
{
    public function __construct(
        private readonly HandlerRegistry $registry,
        private readonly TokenFactory $tokens,
    ) {}

    /**
     * Connect proxy endpoint.
     *
     * The package ships a stub that returns HTTP 400. Publish the routes
     * and implement your own connect logic — connect requires app-level
     * authentication that the package cannot know about.
     *
     * Recommended implementation pattern:
     *
     *   public function connect(ConnectProxyRequest $request): JsonResponse
     *   {
     *       $user  = auth()->user();
     *       $token = app(TokenFactory::class)->forUser(
     *           userId: $user?->id,
     *           client: $request->clientId(),  // <-- bind token to this connection
     *       );
     *       return response()->json(['result' => ['token' => $token]]);
     *   }
     */
    public function connect(ConnectProxyRequest $request): JsonResponse
    {
        return response()->json(status: Response::HTTP_BAD_REQUEST);
    }

    /**
     * Subscribe proxy endpoint.
     *
     * Routes the request to the registered handler for the channel namespace.
     * The response is always HTTP 200 with a Centrifugo-protocol JSON body —
     * never a raw 4xx/5xx, which Centrifugo would treat as a server error.
     */
    public function subscribe(SubscribeProxyRequest $request): JsonResponse
    {
        $dto = $request->dto();

        try {
            if (!$this->registry->has($dto->namespace)) {
                return response()->json(
                    SubscribeProxyResponse::withError(new ErrorResponse(404, 'Unknown channel namespace'))
                );
            }

            $handler = $this->registry->resolve($dto->namespace);
            $result  = $handler->subscribe($dto);

            $proxyResponse = match (true) {
                $result instanceof SubscribeResult    => SubscribeProxyResponse::withResult($result),
                $result instanceof ErrorResponse      => SubscribeProxyResponse::withError($result),
                $result instanceof DisconnectResponse => SubscribeProxyResponse::withDisconnect($result),
            };

            return response()->json($proxyResponse);

        } catch (Throwable $e) {
            Log::error('[centrifugo] subscribe error', [
                'namespace' => $dto->namespace,
                'error'     => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json(
                SubscribeProxyResponse::withError(new ErrorResponse(500, 'Internal server error'))
            );
        }
    }

    /**
     * RPC proxy endpoint.
     *
     * The 'client' field is validated as required + non-empty by RpcProxyRequest,
     * so the rate-limit cache key is always built from a valid string — never
     * an empty key that would make all clients share one bucket.
     */
    public function rpc(RpcProxyRequest $request): JsonResponse
    {
        $dto = $request->dto();

        if ($this->isRateLimited($dto->client)) {
            Log::warning('[centrifugo] rpc rate limited', [
                'client'    => $dto->client,
                'namespace' => $dto->rpcNamespace,
                'method'    => $dto->rpcMethod,
            ]);

            return response()->json(
                new RpcProxyResponse(new RpcResponseDto(error: new ErrorResponse(429, 'Too many requests')))
            );
        }

        try {
            if (!$this->registry->has($dto->rpcNamespace)) {
                return response()->json(
                    new RpcProxyResponse(new RpcResponseDto(error: new ErrorResponse(404, 'Unknown RPC namespace')))
                );
            }

            $handler = $this->registry->resolve($dto->rpcNamespace);
            $result  = $handler->rpc($dto);

            return response()->json(new RpcProxyResponse($result));

        } catch (Throwable $e) {
            Log::error('[centrifugo] rpc error', [
                'namespace' => $dto->rpcNamespace,
                'method'    => $dto->rpcMethod,
                'error'     => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json(
                new RpcProxyResponse(new RpcResponseDto(error: new ErrorResponse(500, 'Internal server error')))
            );
        }
    }

    /**
     * Refresh proxy endpoint.
     *
     * Default implementation re-issues a connection token for the user.
     * Publish the routes and override this method to add revocation checks.
     * Return a disconnect body to reject the refresh cleanly (avoids Centrifugo retries).
     */
    public function refresh(RefreshProxyRequest $request): JsonResponse
    {
        $dto = $request->dto();

        if ($dto->userId === null) {
            return response()->json([
                'disconnect' => ['code' => 4000, 'reason' => 'Unauthorized'],
            ]);
        }

        $token = $this->tokens->forUser($dto->userId, $dto->client);

        return response()->json(['result' => ['token' => $token]]);
    }

    /**
     * Sub-refresh proxy endpoint.
     *
     * Default implementation re-issues a subscription token for the channel.
     * Publish the routes and override to add per-channel revocation logic.
     */
    public function subRefresh(SubRefreshProxyRequest $request): JsonResponse
    {
        $dto = $request->dto();

        if ($dto->userId === null) {
            return response()->json([
                'disconnect' => ['code' => 4000, 'reason' => 'Unauthorized'],
            ]);
        }

        $token = $this->tokens->forSubscription($dto->userId, $dto->channel);

        return response()->json(['result' => ['token' => $token]]);
    }

    private function isRateLimited(string $client): bool
    {
        $max    = (int) config('centrifugo.rpc_rate_limit.max', 30);
        $window = (int) config('centrifugo.rpc_rate_limit.window', 60);
        $key    = 'centrifugo_rpc_rl:' . $client;

        return RateLimiter::hit($key, $window) > $max;
    }
}
