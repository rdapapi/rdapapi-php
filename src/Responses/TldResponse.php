<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldResponse
{
    public function __construct(
        public TldEntry $data,
        public TldMeta $meta,
        public ?string $etag = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload, ?string $etag = null): self
    {
        /** @var array<string, mixed> $entry */
        $entry = $payload['data'] ?? [];

        /** @var array<string, mixed> $meta */
        $meta = $payload['meta'] ?? [];

        return new self(
            data: TldEntry::fromArray($entry),
            meta: TldMeta::fromArray($meta),
            etag: $etag,
        );
    }
}
