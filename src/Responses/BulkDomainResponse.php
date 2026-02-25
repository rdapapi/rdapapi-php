<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class BulkDomainResponse
{
    /**
     * @param  list<BulkDomainResult>  $results
     */
    public function __construct(
        public array $results,
        public BulkDomainSummary $summary,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            results: array_values(array_map(
                fn (array $r): BulkDomainResult => BulkDomainResult::fromArray($r),
                $data['results'] ?? [],
            )),
            summary: BulkDomainSummary::fromArray($data['summary'] ?? []),
        );
    }
}
