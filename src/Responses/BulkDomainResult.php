<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class BulkDomainResult
{
    public function __construct(
        public string $domain,
        public string $status,
        public ?DomainResponse $data = null,
        public ?string $error = null,
        public ?string $message = null,
        public ?string $reason = null,
        public ?string $registrable_domain = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['domain'],
            status: $data['status'],
            data: isset($data['data']) ? DomainResponse::fromArray($data['data']) : null,
            error: $data['error'] ?? null,
            message: $data['message'] ?? null,
            reason: $data['reason'] ?? null,
            registrable_domain: $data['registrable_domain'] ?? null,
        );
    }
}
