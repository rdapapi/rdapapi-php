<?php

declare(strict_types=1);

namespace RdapApi\Responses;

/**
 * How often each common domain field is populated in a TLD's RDAP responses.
 *
 * Each value is one of `always`, `usually`, `sometimes`, or `never`.
 */
final readonly class FieldAvailability
{
    public function __construct(
        public string $registrar,
        public string $registered_at,
        public string $expires_at,
        public string $nameservers,
        public string $status,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registrar: (string) ($data['registrar'] ?? ''),
            registered_at: (string) ($data['registered_at'] ?? ''),
            expires_at: (string) ($data['expires_at'] ?? ''),
            nameservers: (string) ($data['nameservers'] ?? ''),
            status: (string) ($data['status'] ?? ''),
        );
    }
}
