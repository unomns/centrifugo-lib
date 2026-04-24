<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class ErrorResponse
{
    public function __construct(
        public int $code,
        public string $message,
    ) {}

    public static function withCode(int $code, string $message): self
    {
        return new self($code, $message);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code'    => $this->code,
            'message' => $this->message,
        ];
    }
}
