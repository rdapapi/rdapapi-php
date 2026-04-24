<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\NotSupportedException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\SubscriptionRequiredException;
use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

try {
    $domain = $api->domain('example.nope');
} catch (NotSupportedException $e) {
    // Catch before NotFoundException: it's a subclass.
    echo "TLD not covered by RDAP: {$e->getMessage()}\n";
} catch (NotFoundException $e) {
    echo "Domain not registered: {$e->getMessage()} (code: {$e->errorCode})\n";
} catch (RateLimitException $e) {
    echo "Rate limited, retry after {$e->retryAfter} seconds\n";
} catch (AuthenticationException $e) {
    echo "Invalid API key\n";
} catch (SubscriptionRequiredException $e) {
    echo "Subscription required. Visit https://rdapapi.io/pricing\n";
}
