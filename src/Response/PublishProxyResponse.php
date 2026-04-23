<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

use JsonSerializable;

readonly class PublishProxyResponse implements JsonSerializable
{
    private function __construct(
        private ?PublishResult      $result     = null,
        private ?ErrorResponse      $error      = null,
        private ?DisconnectResponse $disconnect = null,
    ) {}

    public static function withResult(PublishResult $result): self
    {
        return new self(result: $result);
    }

    public static function withError(ErrorResponse $error): self
    {
        return new self(error: $error);
    }

    public static function withDisconnect(DisconnectResponse $disconnect): self
    {
        return new self(disconnect: $disconnect);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        if ($this->error !== null) {
            return ['error' => $this->error->toArray()];
        }
        if ($this->disconnect !== null) {
            return ['disconnect' => $this->disconnect->toArray()];
        }
        return ['result' => $this->result->toArray()];
    }
}
