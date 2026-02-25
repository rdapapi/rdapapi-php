<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class NameserverResponse
{
    /**
     * @param  list<string>  $status
     */
    public function __construct(
        public string $ldh_name,
        public ?string $unicode_name,
        public ?string $handle,
        public IpAddresses $ip_addresses,
        public array $status,
        public Dates $dates,
        public Entities $entities,
        public Meta $meta,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ldh_name: $data['ldh_name'],
            unicode_name: $data['unicode_name'] ?? null,
            handle: $data['handle'] ?? null,
            ip_addresses: IpAddresses::fromArray($data['ip_addresses'] ?? []),
            status: $data['status'] ?? [],
            dates: Dates::fromArray($data['dates'] ?? []),
            entities: Entities::fromArray($data['entities'] ?? []),
            meta: Meta::fromArray($data['meta'] ?? []),
        );
    }
}
