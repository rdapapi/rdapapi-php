<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Entities
{
    public function __construct(
        public ?Contact $registrant = null,
        public ?Contact $administrative = null,
        public ?Contact $technical = null,
        public ?Contact $billing = null,
        public ?Contact $abuse = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registrant: isset($data['registrant']) ? Contact::fromArray($data['registrant']) : null,
            administrative: isset($data['administrative']) ? Contact::fromArray($data['administrative']) : null,
            technical: isset($data['technical']) ? Contact::fromArray($data['technical']) : null,
            billing: isset($data['billing']) ? Contact::fromArray($data['billing']) : null,
            abuse: isset($data['abuse']) ? Contact::fromArray($data['abuse']) : null,
        );
    }
}
