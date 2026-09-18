<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class IpResponse
{
    /**
     * @param  list<string>  $status
     * @param  list<string>  $cidr
     * @param  string|null  $geofeed  RFC 8805 geofeed URL as the network publishes it, never fetched and never inherited from a parent.
     * @param  list<Remark>  $remarks
     */
    public function __construct(
        public ?string $handle,
        public ?string $name,
        public ?string $type,
        public ?string $start_address,
        public ?string $end_address,
        public ?string $ip_version,
        public ?string $parent_handle,
        public ?string $country,
        public array $status,
        public Dates $dates,
        public Entities $entities,
        public array $cidr,
        public ?string $geofeed,
        public array $remarks,
        public ?string $port43,
        public ?Redaction $redacted,
        public Meta $meta,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: $data['handle'] ?? null,
            name: $data['name'] ?? null,
            type: $data['type'] ?? null,
            start_address: $data['start_address'] ?? null,
            end_address: $data['end_address'] ?? null,
            ip_version: $data['ip_version'] ?? null,
            parent_handle: $data['parent_handle'] ?? null,
            country: $data['country'] ?? null,
            status: $data['status'] ?? [],
            dates: Dates::fromArray($data['dates'] ?? []),
            entities: Entities::fromArray($data['entities'] ?? []),
            cidr: $data['cidr'] ?? [],
            geofeed: $data['geofeed'] ?? null,
            remarks: array_values(array_map(
                fn (array $r): Remark => Remark::fromArray($r),
                $data['remarks'] ?? [],
            )),
            port43: $data['port43'] ?? null,
            redacted: isset($data['redacted']) ? Redaction::fromArray($data['redacted']) : null,
            meta: Meta::fromArray($data['meta'] ?? []),
        );
    }
}
