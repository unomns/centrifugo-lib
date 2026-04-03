<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Dto;

readonly class RpcRequestDto
{
    public function __construct(
        public string $userId,
        public string $rpcNamespace,
        public string $rpcMethod,
        public array  $data,
        public string $client,
    ) {}
}
