<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class SubscribeResult
{
    /**
     * @param array<string, mixed>|null $info
     * @param array<string, mixed>|null $data
     */
    public function __construct(
        public ?array           $info      = null,
        public ?string          $b64info   = null,
        public ?array           $data      = null,
        public ?string          $b64data   = null,
        public ?OverrideOptions $override  = null,
        public ?int             $expire_at = null,
        public ?string          $token     = null,
    ) {}

    public static function allow(?OverrideOptions $override = null): self
    {
        return new self(override: $override);
    }

    public static function allowWithToken(string $token, ?OverrideOptions $override = null): self
    {
        return new self(token: $token, override: $override);
    }

    public static function allowWithOverride(OverrideOptions $override): self
    {
        return new self(override: $override);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'info'      => $this->info,
            'b64info'   => $this->b64info,
            'data'      => $this->data,
            'b64data'   => $this->b64data,
            'override'  => $this->override?->toArray(),
            'expire_at' => $this->expire_at,
            'token'     => $this->token,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
