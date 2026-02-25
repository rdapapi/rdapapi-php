<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Registrar
{
    public function __construct(
        public ?string $name = null,
        public ?string $iana_id = null,
        public ?string $abuse_email = null,
        public ?string $abuse_phone = null,
        public ?string $url = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            iana_id: $data['iana_id'] ?? null,
            abuse_email: $data['abuse_email'] ?? null,
            abuse_phone: $data['abuse_phone'] ?? null,
            url: $data['url'] ?? null,
        );
    }
}
