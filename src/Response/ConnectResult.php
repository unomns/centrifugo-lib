<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class ConnectResult
{
    /**
     * @param array<string, mixed>|null    $info
     * @param array<string, mixed>|null    $data
     * @param list<string>|null            $channels
     * @param array<string, SubscribeOptions>|null $subs
     * @param array<string, mixed>|null    $meta
     */
    public function __construct(
        public string  $user      = '',
        public ?string $token     = null,
        public ?int    $expire_at = null,
        public ?array  $info      = null,
        public ?string $b64info   = null,
        public ?array  $data      = null,
        public ?string $b64data   = null,
        public ?array  $channels  = null,
        public ?array  $subs      = null,
        public ?array  $meta      = null,
    ) {}

    /** Authenticate a known user, optionally expiring at a Unix timestamp. */
    public static function withUser(string $userId, ?int $expireAt = null): self
    {
        return new self(user: $userId, expire_at: $expireAt);
    }

    /**
     * Return a connection token instead of a user ID.
     * Centrifugo will validate the JWT and extract the user from it.
     */
    public static function withToken(string $token): self
    {
        return new self(token: $token);
    }

    /** Allow an anonymous (unauthenticated) connection. */
    public static function anonymous(): self
    {
        return new self(user: '');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = array_filter([
            'token'     => $this->token,
            'expire_at' => $this->expire_at,
            'info'      => $this->info,
            'b64info'   => $this->b64info,
            'data'      => $this->data,
            'b64data'   => $this->b64data,
            'channels'  => $this->channels,
            'meta'      => $this->meta,
        ], static fn (mixed $v): bool => $v !== null);

        if ($this->subs !== null) {
            $result['subs'] = array_map(
                static fn (SubscribeOptions $opt): array => $opt->toArray(),
                $this->subs,
            );
        }

        return ['user' => $this->user, ...$result];
    }
}
