<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class DomainResponse
{
    /**
     * @param  list<string>  $status
     * @param  list<string>  $nameservers
     */
    public function __construct(
        public string $domain,
        public ?string $unicode_name,
        public ?string $handle,
        public array $status,
        public Registrar $registrar,
        public Dates $dates,
        public array $nameservers,
        public bool $dnssec,
        public Entities $entities,
        public Meta $meta,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['domain'],
            unicode_name: $data['unicode_name'] ?? null,
            handle: $data['handle'] ?? null,
            status: $data['status'] ?? [],
            registrar: Registrar::fromArray($data['registrar'] ?? []),
            dates: Dates::fromArray($data['dates'] ?? []),
            nameservers: $data['nameservers'] ?? [],
            dnssec: $data['dnssec'] ?? false,
            entities: Entities::fromArray($data['entities'] ?? []),
            meta: Meta::fromArray($data['meta'] ?? []),
        );
    }
}
