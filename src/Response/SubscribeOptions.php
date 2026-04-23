<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

/**
 * Per-channel subscription options returned inside ConnectResult::$subs.
 *
 * Use this when the connect proxy wants to subscribe the client to server-side
 * channels with specific options at connect time.
 */
readonly class SubscribeOptions
{
    /**
     * @param array<string, mixed>|null $info
     * @param array<string, mixed>|null $data
     */
    public function __construct(
        public ?array           $info     = null,
        public ?string          $b64info  = null,
        public ?array           $data     = null,
        public ?string          $b64data  = null,
        public ?OverrideOptions $override = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'info'     => $this->info,
            'b64info'  => $this->b64info,
            'data'     => $this->data,
            'b64data'  => $this->b64data,
            'override' => $this->override?->toArray(),
        ], static fn (mixed $v): bool => $v !== null);
    }
}
