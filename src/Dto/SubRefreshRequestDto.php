<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class SubRefreshRequestDto
{
    public function __construct(
        public string  $client,
        public string  $channel,
        public ?string $userId,
    ) {}
}
