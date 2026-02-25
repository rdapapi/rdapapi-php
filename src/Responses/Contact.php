<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Contact
{
    public function __construct(
        public ?string $handle = null,
        public ?string $name = null,
        public ?string $organization = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $contact_url = null,
        public ?string $country_code = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: $data['handle'] ?? null,
            name: $data['name'] ?? null,
            organization: $data['organization'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            contact_url: $data['contact_url'] ?? null,
            country_code: $data['country_code'] ?? null,
        );
    }
}
