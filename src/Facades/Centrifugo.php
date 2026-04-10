<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Facades;

use Illuminate\Support\Facades\Facade;
use Unomns\Centrifugo\CentrifugoManager;

/**
 * @method static mixed  publish(string $channel, array $data, bool $skipHistory = false)
 * @method static mixed  broadcast(array $channels, array $data, bool $skipHistory = false)
 * @method static mixed  history(string $channel, int $limit = 0, bool $reverse = false)
 * @method static mixed  presenceStats(string $channel)
 * @method static mixed  subscribe(string $channel, string $user, string $client = '')
 * @method static mixed  unsubscribe(string $channel, string $user)
 * @method static mixed  disconnect(string $user)
 * @method static string generateConnectionToken(string|int|null $userId, int $exp = 0, array $info = [])
 * @method static string generateSubscriptionToken(string|int|null $userId, string $channel, int $exp = 0)
 * @method static string secret()
 *
 * @see CentrifugoManager
 */
class Centrifugo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'centrifugo';
    }
}
