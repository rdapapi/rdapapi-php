<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class TldEntry
{
    public function __construct(
        public string $tld,
        public string $supported_since,
        public string $rdap_server_host,
        public string $rdap_server_url,
        public ?FieldAvailability $field_availability = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tld: (string) ($data['tld'] ?? ''),
            supported_since: (string) ($data['supported_since'] ?? ''),
            rdap_server_host: (string) ($data['rdap_server_host'] ?? ''),
            rdap_server_url: (string) ($data['rdap_server_url'] ?? ''),
            field_availability: isset($data['field_availability']) && is_array($data['field_availability'])
                ? FieldAvailability::fromArray($data['field_availability'])
                : null,
        );
    }
}
