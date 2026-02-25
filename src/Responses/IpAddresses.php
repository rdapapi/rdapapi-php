<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class IpAddresses
{
    /**
     * @param  list<string>  $v4
     * @param  list<string>  $v6
     */
    public function __construct(
        public array $v4 = [],
        public array $v6 = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            v4: $data['v4'] ?? [],
            v6: $data['v6'] ?? [],
        );
    }
}
