<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

use JsonSerializable;
use Unomns\Centrifugo\Dto\RpcResponseDto;

readonly class RpcProxyResponse implements JsonSerializable
{
    public function __construct(private RpcResponseDto $dto) {}

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        if ($this->dto->hasError()) {
            return ['error' => $this->dto->error->toArray()];
        }

        $result = [];

        if (!empty($this->dto->data)) {
            $result['data'] = $this->dto->data;
        }

        if ($this->dto->b64data !== null) {
            $result['b64data'] = $this->dto->b64data;
        }

        return ['result' => $result ?: (object) []];
    }
}
