<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class ConnectRequestDto
{
    /**
     * @param array<string, mixed>|null $data
     * @param list<string>|null         $channels
     */
    public function __construct(
        public string  $client,
        public string  $transport,
        public string  $protocol,
        public string  $encoding,
        public ?string $name     = null,
        public ?string $version  = null,
        public ?array  $data     = null,
        public ?string $b64data  = null,
        public ?array  $channels = null,
    ) {}
}
