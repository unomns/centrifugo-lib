<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class RefreshResult
{
    /** @param array<string, mixed>|null $info */
    public function __construct(
        public ?string $token     = null,
        public ?bool   $expired   = null,
        public ?int    $expire_at = null,
        public ?array  $info      = null,
        public ?string $b64info   = null,
    ) {}

    /** Re-issue a connection token (token-proxy auth flow). */
    public static function withToken(string $token): self
    {
        return new self(token: $token);
    }

    /**
     * Extend the connection, expiring at the given Unix timestamp.
     *
     * @param array<string, mixed>|null $info
     */
    public static function withExpiry(int $expireAt, ?array $info = null): self
    {
        return new self(expire_at: $expireAt, info: $info);
    }

    /** Immediately expire the connection. */
    public static function expired(): self
    {
        return new self(expired: true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'token'     => $this->token,
            'expired'   => $this->expired,
            'expire_at' => $this->expire_at,
            'info'      => $this->info,
            'b64info'   => $this->b64info,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
