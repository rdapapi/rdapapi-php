<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldListResponse
{
    /**
     * @param  list<TldEntry>  $data
     */
    public function __construct(
        public array $data,
        public TldListMeta $meta,
        public ?string $etag = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload, ?string $etag = null): self
    {
        /** @var list<array<string, mixed>> $entries */
        $entries = $payload['data'] ?? [];

        /** @var array<string, mixed> $meta */
        $meta = $payload['meta'] ?? [];

        return new self(
            data: array_map(
                fn (array $row): TldEntry => TldEntry::fromArray($row),
                $entries,
            ),
            meta: TldListMeta::fromArray($meta),
            etag: $etag,
        );
    }
}
