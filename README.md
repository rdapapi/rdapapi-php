# rdapapi-php

Official PHP SDK for the [RDAP API](https://rdapapi.io) — look up domains, IP addresses, ASNs, nameservers, and entities via the RDAP protocol.

[![Packagist Version](https://img.shields.io/packagist/v/rdapapi/rdapapi-php.svg)](https://packagist.org/packages/rdapapi/rdapapi-php)
[![PHP Version](https://img.shields.io/packagist/php-v/rdapapi/rdapapi-php.svg)](https://packagist.org/packages/rdapapi/rdapapi-php)
[![CI](https://github.com/rdapapi/rdapapi-php/actions/workflows/ci.yml/badge.svg)](https://github.com/rdapapi/rdapapi-php/actions/workflows/ci.yml)

## Installation

```bash
composer require rdapapi/rdapapi-php
```

Requires PHP 8.2 or later.

## Quick Start

```php
<?php

use RdapApi\RdapApi;

$api = new RdapApi('your-api-key');

$domain = $api->domain('google.com');

echo $domain->registrar->name;     // "MarkMonitor Inc."
echo $domain->dates->registered;   // "1997-09-15T04:00:00Z"
echo $domain->dates->expires;      // "2028-09-14T04:00:00Z"
print_r($domain->nameservers);     // ["ns1.google.com", ...]
```

## Usage

### Configuration

```php
use RdapApi\RdapApi;

// Default configuration
$api = new RdapApi('your-api-key');

// Custom timeout (in seconds)
$api = new RdapApi('your-api-key', ['timeout' => 10]);

// Custom base URL
$api = new RdapApi('your-api-key', ['base_url' => 'https://custom.api.com/v1']);
```

### Domain Lookup

```php
$domain = $api->domain('example.com');
echo $domain->domain;              // "example.com"
echo $domain->registrar->name;     // Registrar name
echo $domain->registrar->iana_id;  // IANA registrar ID
echo $domain->dnssec;              // true/false

// With registrar follow-through (for thin registries)
$domain = $api->domain('example.com', ['follow' => true]);
echo $domain->meta->followed;      // true
```

### IP Address Lookup

```php
$ip = $api->ip('8.8.8.8');
echo $ip->name;            // "LVLT-GOGL-8-8-8"
echo $ip->country;         // "US"
print_r($ip->cidr);        // ["8.8.8.0/24"]
echo $ip->start_address;   // "8.8.8.0"
echo $ip->end_address;     // "8.8.8.255"
```

### ASN Lookup

```php
$asn = $api->asn(15169);           // integer
$asn = $api->asn('AS15169');       // string with prefix (stripped automatically)

echo $asn->name;           // "GOOGLE"
echo $asn->start_autnum;   // 15169
```

### Nameserver Lookup

```php
$ns = $api->nameserver('ns1.google.com');
echo $ns->ldh_name;                // "ns1.google.com"
print_r($ns->ip_addresses->v4);   // ["216.239.32.10"]
print_r($ns->ip_addresses->v6);   // ["2001:4860:4802:32::a"]
```

### Entity Lookup

```php
$entity = $api->entity('GOGL');
echo $entity->name;                    // "Google LLC"
echo $entity->organization;            // "Google LLC"
echo $entity->autnums[0]->handle;     // "AS15169"
echo $entity->networks[0]->cidr[0];   // "8.8.8.0/24"
```

### Bulk Domain Lookup

Requires a Pro or Business plan. Up to 10 domains per call.

```php
$resp = $api->bulkDomains(
    ['google.com', 'github.com', 'example.com'],
    ['follow' => true],
);

echo $resp->summary->total;        // 3
echo $resp->summary->successful;   // 3

foreach ($resp->results as $result) {
    if ($result->status === 'success') {
        echo "{$result->domain} — {$result->data->registrar->name}\n";
    } else {
        echo "{$result->domain} — error: {$result->message}\n";
    }
}
```

## Error Handling

All API errors are thrown as typed exceptions that extend `RdapApiException`:

```php
use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\SubscriptionRequiredException;

try {
    $domain = $api->domain('example.com');
} catch (NotFoundException $e) {
    echo "Not found: {$e->getMessage()}";
} catch (RateLimitException $e) {
    echo "Rate limited, retry after {$e->retryAfter} seconds";
} catch (AuthenticationException $e) {
    echo "Invalid API key";
} catch (SubscriptionRequiredException $e) {
    echo "Subscription required";
}
```

| Exception | HTTP Status | Description |
|---|---|---|
| `ValidationException` | 400 | Invalid input |
| `AuthenticationException` | 401 | Invalid or missing API key |
| `SubscriptionRequiredException` | 403 | No active subscription |
| `NotFoundException` | 404 | No RDAP data found |
| `RateLimitException` | 429 | Rate limit or quota exceeded |
| `UpstreamException` | 502 | Upstream RDAP server failure |
| `TemporarilyUnavailableException` | 503 | Domain data temporarily unavailable |

All exceptions expose `statusCode`, `errorCode`, and `getMessage()`. `RateLimitException` and `TemporarilyUnavailableException` also have `retryAfter` (int or null).

## Nullable Fields

Fields that may be absent in API responses use nullable types (`?string`, `?int`). Check for null before using:

```php
if ($domain->dates->expires !== null) {
    echo "Expires: {$domain->dates->expires}";
}

// Or use PHP 8's nullsafe operator
echo $domain->entities->registrant?->name;
```

## Development

Set up pre-commit hooks (runs lint + tests before each commit):

```bash
git config core.hooksPath .githooks
```

## License

MIT — see [LICENSE](LICENSE).
