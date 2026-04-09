<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Response;

readonly class OverrideOptions
{
    public function __construct(
        public ?bool $presence              = null,
        public ?bool $join_leave            = null,
        public ?bool $force_push_join_leave = null,
        public ?bool $force_positioning     = null,
        public ?bool $force_recovery        = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'presence'              => $this->presence,
            'join_leave'            => $this->join_leave,
            'force_push_join_leave' => $this->force_push_join_leave,
            'force_positioning'     => $this->force_positioning,
            'force_recovery'        => $this->force_recovery,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
