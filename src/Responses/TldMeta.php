<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldMeta
{
    public function __construct(
        public string $computed_at,
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
            thresholds: TldThresholds::fromArray($thresholds),
        );
    }
}
