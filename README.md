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
var_dump($domain->dnssec);         // true, false, or null when the registry publishes no status

// With registrar follow-through (for thin registries)
$domain = $api->domain('example.com', ['follow' => true]);
echo $domain->meta->followed;      // true
```

The TLDs with no RDAP server — `.it`, `.eu` and `.tr` among them — are read from the
registry's WHOIS server and come back in the same shape. `meta->source` says which
protocol answered and `meta->server` names the host that did:

```php
$domain = $api->domain('example.it');
echo $domain->meta->source;        // "whois"
echo $domain->meta->server;        // "whois.nic.it"

// Refuse the fallback: those TLDs then raise NotSupportedException.
$domain = $api->domain('example.it', ['whois' => false]);
```

`meta->rdap_server` is deprecated and null when WHOIS answered — use `meta->server`, which
names the host over either protocol. `meta->raw_rdap_url` is the RDAP URL that was fetched,
so it too is null whenever `source` is `whois`.

### Redacted Fields

Most contact fields come back `null`, and a field the registry never collected looks exactly
like one it withheld. A server that declares the difference (RFC 9537) gets reported in
`redacted`, on every response type. It mirrors the record, so a claim about
`entities->registrant->name` sits at `redacted->entities['registrant']['name']`:

```php
$domain = $api->domain('google.co.uk');

$method = $domain->redacted?->entities['registrant']['email'] ?? null;

if ($method === 'replacementValue') {
    echo 'The email is a substitute, not the real one.';
}
```

The methods are `removal`, `emptyValue`, `partialValue` and `replacementValue`. They stay
plain strings, so a method we do not recognise reaches you unchanged. `redacted` is `null`
when the server declared nothing — which is not evidence that nothing was withheld.

### IP Address Lookup

```php
$ip = $api->ip('8.8.8.8');
echo $ip->name;            // "LVLT-GOGL-8-8-8"
echo $ip->country;         // "US"
print_r($ip->cidr);        // ["8.8.8.0/24"]
echo $ip->start_address;   // "8.8.8.0"
echo $ip->end_address;     // "8.8.8.255"
echo $ip->geofeed;         // RFC 8805 geofeed URL, or null

// A CIDR block returns that network, so different prefix lengths can
// return different allocations.
$ip = $api->ip('8.8.8.0/24');
```

`geofeed` is returned exactly as the network publishes it — never fetched, never inherited
from a parent network.

### ASN Lookup

```php
$asn = $api->asn(15169);           // integer
$asn = $api->asn('AS15169');       // string with prefix (stripped automatically)

echo $asn->name;           // "GOOGLE"
echo $asn->start_autnum;   // 15169
echo $asn->country;        // "US", or null when no contact supplies one
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

Requires a Pro or Business plan. Up to 10 domains per call. `follow` and `whois` apply to
every domain in the request.

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

## Supported TLDs Catalog

List every TLD the API can resolve, with the protocol that answers for it, the date support was added, and a qualitative summary of which fields the registry's RDAP server populates. Does not count against your monthly quota.

```php
$tlds = $api->tlds();
if ($tlds !== null) {
    echo "{$tlds->meta->count} TLDs, coverage ".round($tlds->meta->coverage * 100)."%\n";

    foreach ($tlds->data as $tld) {
        echo "{$tld->tld} answered by {$tld->server} over {$tld->protocol}\n";

        $availability = $tld->field_availability;
        if ($availability !== null) {
            echo "{$tld->tld}: expires_at={$availability->expires_at}\n";
        }
    }
}
```

`protocol` is `rdap` or `whois`; a `whois` entry has no `rdap_server_host` or
`rdap_server_url` (both null) and no `field_availability`, which is measured from RDAP
responses only. `rdap_server_host` is deprecated in favour of `server`.

Filter to recent additions or to a single registry:

```php
$recent = $api->tlds(['since' => '2026-04-01T00:00:00Z']);
$verisign = $api->tlds(['server' => 'rdap.verisign.com']);
```

Pass back the previous `etag` to skip the transfer when nothing has changed:

```php
$first = $api->tlds();
$later = $api->tlds(['if_none_match' => $first?->etag ?? '']);
if ($later === null) {
    echo "No change since last poll\n";
}
```

Look up a single TLD:

```php
$com = $api->tld('com');
echo $com->data->server; // "rdap.verisign.com"
```

## Health Check

`ping()` costs no quota and makes no upstream call. It never throws: an unreachable host or
an error response comes back as `false`, which is what a liveness probe is asking about.

```php
if ($api->ping()) {
    echo 'The API is reachable';
}
```

## Error Handling

All API errors are thrown as typed exceptions that extend `RdapApiException`:

```php
use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\NotSupportedException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\SubscriptionRequiredException;

try {
    $domain = $api->domain('example.nope');
} catch (NotSupportedException $e) {
    // Catch before NotFoundException: it's a subclass.
    echo 'The TLD is not covered by RDAP.';
} catch (NotFoundException $e) {
    echo 'The domain is not registered.';
} catch (RateLimitException $e) {
    echo "Rate limited, retry after {$e->retryAfter} seconds";
} catch (AuthenticationException $e) {
    echo 'Invalid API key';
} catch (SubscriptionRequiredException $e) {
    echo match ($e->errorCode) {
        'plan_upgrade_required' => 'Bulk lookups need a Pro or Business plan.',
        'forbidden' => 'This IP is temporarily blocked. It lifts on its own.',
        default => 'Subscription required.',
    };
}
```

`NotSupportedException` extends `NotFoundException`, so catching `NotFoundException` still handles both cases.

`SubscriptionRequiredException` covers every `403`, and one of them is not about billing: `forbidden` is a temporary IP block that lifts on its own and that API rate limits never cause. Branch on `errorCode` before telling anyone to subscribe.

| Exception | HTTP Status | `errorCode` |
|---|---|---|
| `ValidationException` | 400 | `invalid_domain`, `invalid_ip`, `invalid_asn`, `invalid_nameserver`, `invalid_handle`, `invalid_prefix`, `invalid_since`, `bad_request` |
| `AuthenticationException` | 401 | `unauthenticated` |
| `SubscriptionRequiredException` | 403 | `subscription_required`, `plan_upgrade_required` (bulk needs Pro or Business), `forbidden` (a temporary IP block, not a billing state) |
| `NotFoundException` | 404 | `not_found` — the namespace is covered but no record exists |
| `NotSupportedException` | 404 | `not_supported` — nothing we can query covers this TLD, IP range or handle |
| `ValidationException` | 422 | `request_failed` — the body failed validation |
| `RateLimitException` | 429 | `rate_limit_exceeded`, `quota_exceeded`, `too_many_requests` |
| `UpstreamException` | 502 | `lookup_failed`, `bad_gateway` — carries `retryAfter` when the registry named one |
| `TemporarilyUnavailableException` | 503 | `temporarily_unavailable`, `service_unavailable` |
| `RdapApiException` | any other | `method_not_allowed` (405), `payload_too_large` (413), `gateway_timeout` (504), `server_error` |

Branch on `errorCode`, never on the message: messages are display text and may be reworded at any time. Any code above can answer any endpoint.

All exceptions expose `statusCode`, `errorCode`, and `getMessage()`. `RateLimitException`, `TemporarilyUnavailableException` and `UpstreamException` also have `retryAfter`: seconds to wait, read from the `Retry-After` header in either form the HTTP spec allows — a count of seconds, or a date, which is what a throttling registry's own header often carries — and falling back to the body's `retry_after`. It is null when the API gave no estimate.

`ValidationException` has `errors`, the per-field messages a `422` sends (empty on a `400`):

```php
use RdapApi\Exceptions\ValidationException;

try {
    $api->bulkDomains($tooManyDomains);
} catch (ValidationException $e) {
    foreach ($e->errors as $field => $messages) {
        echo "{$field}: ".implode(' ', $messages)."\n";
    }
}
```

## Nullable Fields

Fields that may be absent in API responses use nullable types (`?string`, `?int`, `?bool`). Check for null before using. `null` means the registry published nothing — `dnssec` is `null` where the registry publishes no DNSSEC status, which is not the same as `false`:

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
