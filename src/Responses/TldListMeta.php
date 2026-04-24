<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldListMeta
{
    public function __construct(
        public string $computed_at,
        public int $count,
        public float $coverage,
        public TldThresholds $thresholds,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $thresholds */
        $thresholds = $data['thresholds'] ?? [];

        return new self(
            computed_at: (string) ($data['computed_at'] ?? ''),
            count: (int) ($data['count'] ?? 0),
            coverage: (float) ($data['coverage'] ?? 0),
            thresholds: TldThresholds::fromArray($thresholds),
        );
    }
}
