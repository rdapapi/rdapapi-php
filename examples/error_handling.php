<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\SubscriptionRequiredException;
use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

try {
    $domain = $api->domain('this-domain-does-not-exist.example');
} catch (NotFoundException $e) {
    echo "Domain not found: {$e->getMessage()} (code: {$e->errorCode})\n";
} catch (RateLimitException $e) {
    echo "Rate limited — retry after {$e->retryAfter} seconds\n";
} catch (AuthenticationException $e) {
    echo "Invalid API key\n";
} catch (SubscriptionRequiredException $e) {
    echo "Subscription required — visit https://rdapapi.io/pricing\n";
}
