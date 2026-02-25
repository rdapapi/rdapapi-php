<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class EntityNetwork
{
    /**
     * @param  list<string>  $cidr
     */
    public function __construct(
        public ?string $handle = null,
        public ?string $name = null,
        public ?string $start_address = null,
        public ?string $end_address = null,
        public ?string $ip_version = null,
        public array $cidr = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: $data['handle'] ?? null,
            name: $data['name'] ?? null,
            start_address: $data['start_address'] ?? null,
            end_address: $data['end_address'] ?? null,
            ip_version: $data['ip_version'] ?? null,
            cidr: $data['cidr'] ?? [],
        );
    }
}
