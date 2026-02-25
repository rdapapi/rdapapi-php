<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class BulkDomainSummary
{
    public function __construct(
        public int $total,
        public int $successful,
        public int $failed,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            successful: $data['successful'],
            failed: $data['failed'],
        );
    }
}
