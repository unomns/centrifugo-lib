<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

use JsonSerializable;

/**
 * Centrifugo subscribe proxy response.
 *
 * The protocol requires exactly ONE of: result / error / disconnect.
 * Named static constructors enforce this — there is no way to produce an
 * ambiguous or empty response body through this class.
 *
 * An empty SubscribeResult serialises as {"result": {}} (not null), which
 * Centrifugo interprets as a successful subscription with no initial data.
 */
readonly class SubscribeProxyResponse implements JsonSerializable
{
    private function __construct(
        private ?SubscribeResult    $result     = null,
        private ?ErrorResponse      $error      = null,
        private ?DisconnectResponse $disconnect = null,
    ) {}

    public static function withResult(SubscribeResult $result): self
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

    public function jsonSerialize(): array
    {
        if ($this->error !== null) {
            return ['error' => $this->error->toArray()];
        }

        if ($this->disconnect !== null) {
            return ['disconnect' => $this->disconnect->toArray()];
        }

        $data = $this->result?->toArray() ?? [];

        return ['result' => empty($data) ? (object) [] : $data];
    }
}
