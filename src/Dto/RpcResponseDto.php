<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

use Unomns\Centrifugo\Response\ErrorResponse;

readonly class RpcResponseDto
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public array          $data    = [],
        public ?ErrorResponse $error   = null,
        public ?string        $b64data = null,
    ) {}

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
