<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

class ChannelHelper
{
    public static function general(string $namespace): string
    {
        return $namespace;
    }

    public static function typed(string $namespace, string $type): string
    {
        return "{$namespace}:{$type}";
    }

    public static function dynamic(string $namespace, int|string $id): string
    {
        return "{$namespace}:#{$id}";
    }

    public static function personal(int $userId, string $prefix = 'user'): string
    {
        return "{$prefix}:#{$userId}";
    }
}
