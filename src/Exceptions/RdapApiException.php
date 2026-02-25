<?php

declare(strict_types=1);

namespace RdapApi\Exceptions;

class RdapApiException extends \Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly string $errorCode,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
