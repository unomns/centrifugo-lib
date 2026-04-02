<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

use Illuminate\Support\Facades\Log;
use Unomns\Centrifugo\Contracts\ChannelHandlerInterface;
use Unomns\Centrifugo\Dto\RpcRequestDto;
use Unomns\Centrifugo\Dto\RpcResponseDto;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;
use Unomns\Centrifugo\Response\DisconnectResponse;
use Unomns\Centrifugo\Response\ErrorResponse;
use Unomns\Centrifugo\Response\SubscribeResult;

abstract class AbstractChannelHandler implements ChannelHandlerInterface
{
    public function __construct(
        protected readonly CentrifugoManager $manager,
    ) {}

    abstract public function subscribe(
        SubscribeRequestDto $dto,
    ): SubscribeResult|ErrorResponse|DisconnectResponse;

    /**
     * Default RPC handler — returns a structured 405 error instead of throwing.
     *
     * A 500 response to Centrifugo causes retries; a structured error payload
     * is forwarded to the client cleanly. Override this in handlers that
     * support RPC.
     */
    public function rpc(RpcRequestDto $dto): RpcResponseDto
    {
        $this->logWarning(
            $dto->rpcNamespace,
            'rpc() called on handler that does not support RPC',
            ['method' => $dto->rpcMethod, 'client' => $dto->client],
        );

        return new RpcResponseDto(error: new ErrorResponse(405, 'Method not allowed for this namespace'));
    }

    /**
     * Fetch channel history publications.
     * Returns an empty array on error rather than throwing.
     */
    protected function history(string $channel, int $limit = 50, bool $reverse = true): array
    {
        $result = $this->manager->history($channel, $limit, $reverse);

        if (!empty($result->error)) {
            $this->logWarning($channel, 'history() failed', (array) $result->error);
            return [];
        }

        return $result->result->publications ?? [];
    }

    /**
     * Fetch presence stats for a channel.
     * Returns null on error rather than throwing.
     */
    protected function presenceStat(string $channel): ?object
    {
        $result = $this->manager->presenceStats($channel);

        if (!empty($result->error)) {
            $this->logWarning($channel, 'presenceStats() failed', (array) $result->error);
            return null;
        }

        return $result->result ?? null;
    }

    protected function logWarning(string $channel, string $message, array $context = []): void
    {
        Log::warning("[centrifugo:{$channel}] {$message}", $context);
    }

    protected function logInfo(string $channel, string $message, array $context = []): void
    {
        Log::info("[centrifugo:{$channel}] {$message}", $context);
    }
}
