<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class EntityResponse
{
    /**
     * @param  list<string>  $roles
     * @param  list<string>  $status
     * @param  list<Remark>  $remarks
     * @param  list<PublicId>  $public_ids
     * @param  list<EntityAutnum>  $autnums
     * @param  list<EntityNetwork>  $networks
     */
    public function __construct(
        public ?string $handle,
        public ?string $name,
        public ?string $organization,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $contact_url,
        public ?string $country_code,
        public array $roles,
        public array $status,
        public Dates $dates,
        public array $remarks,
        public ?string $port43,
        public array $public_ids,
        public Entities $entities,
        public array $autnums,
        public array $networks,
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
            organization: $data['organization'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            contact_url: $data['contact_url'] ?? null,
            country_code: $data['country_code'] ?? null,
            roles: $data['roles'] ?? [],
            status: $data['status'] ?? [],
            dates: Dates::fromArray($data['dates'] ?? []),
            remarks: array_values(array_map(fn (array $r): Remark => Remark::fromArray($r), $data['remarks'] ?? [])),
            port43: $data['port43'] ?? null,
            public_ids: array_values(array_map(fn (array $p): PublicId => PublicId::fromArray($p), $data['public_ids'] ?? [])),
            entities: Entities::fromArray($data['entities'] ?? []),
            autnums: array_values(array_map(fn (array $a): EntityAutnum => EntityAutnum::fromArray($a), $data['autnums'] ?? [])),
            networks: array_values(array_map(fn (array $n): EntityNetwork => EntityNetwork::fromArray($n), $data['networks'] ?? [])),
            meta: Meta::fromArray($data['meta'] ?? []),
        );
    }
}
