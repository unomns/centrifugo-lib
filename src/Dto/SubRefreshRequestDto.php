<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class SubRefreshRequestDto
{
    /** @param array<string, mixed>|null $meta */
    public function __construct(
        public string  $client,
        public string  $channel,
        public ?string $userId,
        public string  $transport = '',
        public string  $protocol  = '',
        public string  $encoding  = '',
        public ?array  $meta      = null,
    ) {}
}
