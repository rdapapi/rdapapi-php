<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class AsnResponse
{
    /**
     * @param  list<string>  $status
     * @param  list<Remark>  $remarks
     */
    public function __construct(
        public ?string $handle,
        public ?string $name,
        public ?string $type,
        public ?int $start_autnum,
        public ?int $end_autnum,
        public array $status,
        public Dates $dates,
        public Entities $entities,
        public array $remarks,
        public ?string $port43,
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
            start_autnum: $data['start_autnum'] ?? null,
            end_autnum: $data['end_autnum'] ?? null,
            status: $data['status'] ?? [],
            dates: Dates::fromArray($data['dates'] ?? []),
            entities: Entities::fromArray($data['entities'] ?? []),
            remarks: array_values(array_map(
                fn (array $r): Remark => Remark::fromArray($r),
                $data['remarks'] ?? [],
            )),
            port43: $data['port43'] ?? null,
            meta: Meta::fromArray($data['meta'] ?? []),
        );
    }
}
