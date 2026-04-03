<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class SubscribeRequestDto
{
    public function __construct(
        public string  $userId,
        public string  $namespace,
        public ?string $method,
        public string  $client,
        public array   $meta = [],
        public array   $data = [],
    ) {}
}
