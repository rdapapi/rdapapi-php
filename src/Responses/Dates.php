<?php

declare(strict_types=1);

namespace RdapApi\Responses;

final readonly class Dates
{
    public function __construct(
        public ?string $registered = null,
        public ?string $expires = null,
        public ?string $updated = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registered: $data['registered'] ?? null,
            expires: $data['expires'] ?? null,
            updated: $data['updated'] ?? null,
        );
    }

    /** Parse the registered date into a DateTimeImmutable, or null. */
    public function registeredAt(): ?\DateTimeImmutable
    {
        return self::parse($this->registered);
    }

    /** Parse the expiry date into a DateTimeImmutable, or null. */
    public function expiresAt(): ?\DateTimeImmutable
    {
        return self::parse($this->expires);
    }

    /** Parse the updated date into a DateTimeImmutable, or null. */
    public function updatedAt(): ?\DateTimeImmutable
    {
        return self::parse($this->updated);
    }

    /** Days until expiration, or null if no expiry date is available. */
    public function expiresInDays(): ?int
    {
        $dt = $this->expiresAt();
        if ($dt === null) {
            return null;
        }

        return (int) (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->diff($dt)->format('%r%a');
    }

    private static function parse(?string $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
