<?php

declare(strict_types=1);

namespace RdapApi\Responses;

/**
 * What the upstream server declared it withheld, mirroring the record's shape.
 *
 * Absent from a response when the server declared nothing, which is not evidence
 * that nothing was withheld. Each value is the method used: `removal`,
 * `emptyValue`, `partialValue`, or `replacementValue`. Kept as a plain string,
 * because a server may declare a method we do not recognise.
 */
final readonly class Redaction
{
    /**
     * @param  array<string, string>  $registrar  Claims about the top-level registrar, keyed by field.
     * @param  array<string, array<string, string>>  $entities  Claims keyed by contact role, then by field.
     */
    public function __construct(
        public ?string $handle = null,
        public array $registrar = [],
        public array $entities = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: $data['handle'] ?? null,
            registrar: $data['registrar'] ?? [],
            entities: $data['entities'] ?? [],
        );
    }
}
