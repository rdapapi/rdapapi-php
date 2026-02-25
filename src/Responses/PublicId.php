<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class PublicId
{
    public function __construct(
        public ?string $type = null,
        public ?string $identifier = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            identifier: $data['identifier'] ?? null,
        );
    }
}
