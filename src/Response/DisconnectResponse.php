<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class DisconnectResponse
{
    public function __construct(
        public int $code,
        public string $reason,
    ) {}

    public static function withCode(int $code, string $reason): self
    {
        return new self($code, $reason);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code'   => $this->code,
            'reason' => $this->reason,
        ];
    }
}
