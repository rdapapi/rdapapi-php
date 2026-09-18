<?php

declare(strict_types=1);

namespace RdapApi\Responses;

/**
 * Where the answer came from, and how it was served.
 *
 * `source` is `rdap` or `whois` — always `rdap` except on domain lookups where
 * the TLD has no RDAP server. `rdap_server` is deprecated in favour of `server`
 * and is null when WHOIS answered.
 */
final readonly class Meta
{
    public function __construct(
        public ?string $server,
        public string $source,
        public ?string $rdap_server,
        public ?string $raw_rdap_url,
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
            server: $data['server'] ?? null,
            source: $data['source'] ?? 'rdap',
            rdap_server: $data['rdap_server'] ?? null,
            raw_rdap_url: $data['raw_rdap_url'] ?? null,
            cached: $data['cached'] ?? false,
            cache_expires: $data['cache_expires'] ?? '',
            followed: $data['followed'] ?? null,
            registrar_rdap_server: $data['registrar_rdap_server'] ?? null,
            follow_error: $data['follow_error'] ?? null,
        );
    }
}
