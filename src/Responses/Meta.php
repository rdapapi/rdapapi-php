<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Meta
{
    public function __construct(
        public string $rdap_server,
        public string $raw_rdap_url,
        public bool $cached,
        public string $cache_expires,
        public ?bool $followed = null,
        public ?string $registrar_rdap_server = null,
        public ?string $follow_error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            rdap_server: $data['rdap_server'] ?? '',
            raw_rdap_url: $data['raw_rdap_url'] ?? '',
            cached: $data['cached'] ?? false,
            cache_expires: $data['cache_expires'] ?? '',
            followed: $data['followed'] ?? null,
            registrar_rdap_server: $data['registrar_rdap_server'] ?? null,
            follow_error: $data['follow_error'] ?? null,
        );
    }
}
