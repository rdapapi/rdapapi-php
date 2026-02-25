<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Dates
{
    public function __construct(
        public ?string $registered = null,
        public ?string $expires = null,
        public ?string $updated = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registered: $data['registered'] ?? null,
            expires: $data['expires'] ?? null,
            updated: $data['updated'] ?? null,
        );
    }
}
