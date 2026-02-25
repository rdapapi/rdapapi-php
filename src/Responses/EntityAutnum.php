<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class EntityAutnum
{
    public function __construct(
        public ?string $handle = null,
        public ?string $name = null,
        public ?int $start_autnum = null,
        public ?int $end_autnum = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: $data['handle'] ?? null,
            name: $data['name'] ?? null,
            start_autnum: $data['start_autnum'] ?? null,
            end_autnum: $data['end_autnum'] ?? null,
        );
    }
}
