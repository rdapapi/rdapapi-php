<?php

declare(strict_types=1);

namespace RdapApi\Exceptions;

class NotFoundException extends RdapApiException
{
    /**
     * @param  ?string  $reason  The registry's own wording, sent with `domain_reserved` only.
     * @param  ?string  $registrableDomain  The name to look up instead, sent with `not_registrable` only.
     */
    public function __construct(
        string $message,
        int $statusCode,
        string $errorCode,
        ?\Throwable $previous = null,
        public readonly ?string $reason = null,
        public readonly ?string $registrableDomain = null,
    ) {
        parent::__construct($message, $statusCode, $errorCode, $previous);
    }
}
