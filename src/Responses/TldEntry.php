<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldEntry
{
    /**
     * @param  string  $protocol  Which protocol answers for this TLD: `rdap` or `whois`.
     * @param  string  $server  Hostname of the upstream that answers. What `?server=` filters on, and what a lookup's `meta.server` returns.
     * @param  string|null  $rdap_server_host  Superseded by `server`. Null when `protocol` is `whois`.
     * @param  string|null  $rdap_server_url  Null when `protocol` is `whois`, which has no URL form.
     */
    public function __construct(
        public string $tld,
        public string $protocol,
        public string $supported_since,
        public string $server,
        public ?string $rdap_server_host = null,
        public ?string $rdap_server_url = null,
        public ?FieldAvailability $field_availability = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tld: (string) ($data['tld'] ?? ''),
            protocol: (string) ($data['protocol'] ?? ''),
            supported_since: (string) ($data['supported_since'] ?? ''),
            server: (string) ($data['server'] ?? ''),
            rdap_server_host: $data['rdap_server_host'] ?? null,
            rdap_server_url: $data['rdap_server_url'] ?? null,
            field_availability: isset($data['field_availability']) && is_array($data['field_availability'])
                ? FieldAvailability::fromArray($data['field_availability'])
                : null,
        );
    }
}
