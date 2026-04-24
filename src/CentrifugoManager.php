<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

use phpcent\Client;
use Throwable;

class CentrifugoManager
{
    private ?Client $client = null;

    public function __construct(
        private readonly string $apiUrl,
        private readonly string $apiKey,
        private readonly string $secret,
    ) {}

    public function client(): Client
    {
        return $this->client ??= new Client($this->apiUrl, $this->apiKey, $this->secret);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function publish(string $channel, array $data, bool $skipHistory = false): mixed
    {
        return $this->client()->publish($channel, $data, $skipHistory);
    }

    /**
     * @param list<string>         $channels
     * @param array<string, mixed> $data
     */
    public function broadcast(array $channels, array $data, bool $skipHistory = false): mixed
    {
        return $this->client()->broadcast($channels, $data, $skipHistory);
    }

    public function history(string $channel, int $limit = 0, bool $reverse = false): mixed
    {
        return $this->client()->history($channel, $limit, reverse: $reverse);
    }

    public function presenceStats(string $channel): mixed
    {
        return $this->client()->presenceStats($channel);
    }

    public function subscribe(string $channel, string $user, string $client = ''): mixed
    {
        return $this->client()->subscribe($channel, $user, $client);
    }

    public function unsubscribe(string $channel, string $user): mixed
    {
        return $this->client()->unsubscribe($channel, $user);
    }

    public function disconnect(string $user): mixed
    {
        return $this->client()->disconnect($user);
    }

    /** @param array<string, mixed> $info */
    public function generateConnectionToken(
        string|int|null $userId,
        int $exp = 0,
        array $info = [],
    ): string {
        return $this->client()->generateConnectionToken(
            (string) ($userId ?? ''),
            $exp,
            $info,
        );
    }

    public function generateSubscriptionToken(
        string|int|null $userId,
        string $channel,
        int $exp = 0,
    ): string {
        return $this->client()->generateSubscriptionToken(
            (string) ($userId ?? ''),
            $channel,
            $exp,
        );
    }

    public function secret(): string
    {
        return $this->secret;
    }
}
