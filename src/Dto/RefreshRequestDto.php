<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class RefreshRequestDto
{
    public function __construct(
        public string  $client,
        public ?string $userId,
    ) {}
}
