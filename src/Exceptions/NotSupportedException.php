<?php

declare(strict_types=1);

namespace RdapApi\Exceptions;

/**
 * Thrown when the query targets a namespace not covered by RDAP (HTTP 404).
 *
 * Extends {@see NotFoundException} so existing `catch (NotFoundException $e)`
 * blocks keep working. Catch this class first when you want to distinguish
 * "no RDAP server for this TLD/range" from "namespace covered but no record".
 */
class NotSupportedException extends NotFoundException {}
