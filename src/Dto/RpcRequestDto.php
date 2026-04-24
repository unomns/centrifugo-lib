<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class RpcRequestDto
{
    /**
     * @param array<string, mixed>      $data
     * @param array<string, mixed>|null $meta
     */
    public function __construct(
        public string  $userId,
        public string  $rpcNamespace,
        public string  $rpcMethod,
        public array   $data,
        public string  $client,
        public ?string $b64data = null,
        public ?array  $meta    = null,
    ) {}
}
