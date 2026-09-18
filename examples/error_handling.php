<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\NotSupportedException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\SubscriptionRequiredException;
use RdapApi\Exceptions\ValidationException;
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
    // Every 403 lands here, and `forbidden` is a temporary IP block, not billing.
    echo match ($e->errorCode) {
        'plan_upgrade_required' => "Bulk lookups need a Pro or Business plan.\n",
        'forbidden' => "This IP is temporarily blocked. It lifts on its own.\n",
        default => "Subscription required. Visit https://rdapapi.io/pricing\n",
    };
}

// A 422 names the fields that failed validation.
try {
    $api->bulkDomains(array_fill(0, 11, 'google.com'));
} catch (ValidationException $e) {
    foreach ($e->errors as $field => $messages) {
        echo "{$field}: ".implode(' ', $messages)."\n";
    }
}
