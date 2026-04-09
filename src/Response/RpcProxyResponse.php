<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

use JsonSerializable;
use Unomns\Centrifugo\Dto\RpcResponseDto;

readonly class RpcProxyResponse implements JsonSerializable
{
    public function __construct(private RpcResponseDto $dto) {}

    public function jsonSerialize(): array
    {
        if ($this->dto->hasError()) {
            return ['error' => $this->dto->error->toArray()];
        }

        if (empty($this->dto->data)) {
            return ['result' => (object) []];
        }

        return ['result' => ['data' => $this->dto->data]];
    }
}
