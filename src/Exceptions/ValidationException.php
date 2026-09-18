<?php

declare(strict_types=1);

namespace RdapApi\Exceptions;

class ValidationException extends RdapApiException
{
    /**
     * @param  array<string, list<string>>  $errors  Per-field messages, sent with a 422 `request_failed` only. Empty on a 400.
     */
    public function __construct(
        string $message,
        int $statusCode,
        string $errorCode,
        ?\Throwable $previous = null,
        public readonly array $errors = [],
    ) {
        parent::__construct($message, $statusCode, $errorCode, $previous);
    }
}
