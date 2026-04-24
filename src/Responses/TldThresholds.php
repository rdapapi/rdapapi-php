<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldThresholds
{
    public function __construct(
        public float $always,
        public float $usually,
        public float $sometimes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            always: (float) ($data['always'] ?? 0),
            usually: (float) ($data['usually'] ?? 0),
            sometimes: (float) ($data['sometimes'] ?? 0),
        );
    }
}
