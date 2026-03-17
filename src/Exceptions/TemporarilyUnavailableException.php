<?php

declare(strict_types=1);

namespace RdapApi\Exceptions;

class TemporarilyUnavailableException extends RdapApiException
{
    public function __construct(
        string $message,
        int $statusCode,
        string $errorCode,
        public readonly ?int $retryAfter = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $errorCode, $previous);
    }
}
