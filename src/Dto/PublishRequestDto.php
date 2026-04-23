<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class PublishRequestDto
{
    /**
     * @param array<string, mixed>|null $data
     * @param array<string, mixed>|null $meta
     */
    public function __construct(
        public string  $client,
        public string  $transport,
        public string  $protocol,
        public string  $encoding,
        public string  $user,
        public string  $channel,
        public ?array  $data    = null,
        public ?string $b64data = null,
        public ?array  $meta    = null,
    ) {}
}
