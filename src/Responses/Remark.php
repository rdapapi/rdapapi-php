<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Remark
{
    public function __construct(
        public ?string $title,
        public string $description,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? '',
        );
    }
}
