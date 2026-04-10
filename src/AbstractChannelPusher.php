<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AbstractChannelPusher
{
    public function __construct(
        protected readonly CentrifugoManager $manager,
    ) {}

    /**
     * The Centrifugo namespace this pusher publishes to.
     * Return null when the pusher manages its own channel strings explicitly.
     */
    abstract public function namespace(): ?string;

    /**
     * Publish to an arbitrary channel using the standard payload envelope.
     *
     * All messages published through this package are wrapped as:
     *   ['type' => $type, 'payload' => $payload]
     *
     * This gives clients a consistent shape to dispatch on.
     */
    public function publish(string $channel, string $type, array $payload): void
    {
        try {
            $this->manager->publish($channel, $this->envelope($type, $payload));
        } catch (Throwable $e) {
            $this->logError($channel, $type, $e);
        }
    }

    /**
     * Publish to a user's personal channel.
     * Channel name: "{prefix}:#{userId}"
     */
    public function publishToUser(int $userId, string $type, array $payload): void
    {
        $this->publish($this->personalChannel($userId), $type, $payload);
    }

    /**
     * Broadcast one message to multiple channels simultaneously.
     */
    public function broadcast(array $channels, string $type, array $payload): void
    {
        try {
            $this->manager->broadcast($channels, $this->envelope($type, $payload));
        } catch (Throwable $e) {
            $this->logError(implode(',', $channels), $type, $e);
        }
    }

    /**
     * Broadcast to the personal channels of multiple users.
     */
    public function broadcastToUsers(array $userIds, string $type, array $payload, ?string $prefix = null): void
    {
        $channels = array_map(fn(int $id) => $this->personalChannel($id, $prefix), $userIds);
        $this->broadcast($channels, $type, $payload);
    }

    /**
     * Build the personal channel string for a user.
     * Prefix defaults to config value; pass explicitly to override per-call.
     */
    public function personalChannel(int $userId, ?string $prefix = null): string
    {
        $prefix ??= config('centrifugo.personal_channel_prefix', 'user');

        return "{$prefix}:#{$userId}";
    }

    protected function envelope(string $type, array $payload): array
    {
        return [
            'type'    => $type,
            'payload' => $payload,
        ];
    }

    protected function logError(string $channel, string $type, Throwable $e): void
    {
        Log::error('[centrifugo] publish failed', [
            'channel'   => $channel,
            'type'      => $type,
            'error'     => $e->getMessage(),
            'exception' => $e,
        ]);
    }
}
