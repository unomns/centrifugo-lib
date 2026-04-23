<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class PublishResult
{
    /** @param array<string, mixed>|null $data */
    public function __construct(
        public ?array  $data         = null,
        public ?string $b64data      = null,
        public ?bool   $skip_history = null,
    ) {}

    /** Allow the publication unchanged. */
    public static function allow(): self
    {
        return new self();
    }

    /**
     * Allow but replace the published payload with different data.
     *
     * @param array<string, mixed> $data
     */
    public static function withModifiedData(array $data, bool $skipHistory = false): self
    {
        return new self(data: $data, skip_history: $skipHistory ?: null);
    }

    /** Allow but do not save the message to channel history. */
    public static function skipHistory(): self
    {
        return new self(skip_history: true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'data'         => $this->data,
            'b64data'      => $this->b64data,
            'skip_history' => $this->skip_history,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
