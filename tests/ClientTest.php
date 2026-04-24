<?php

declare(strict_types=1);

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\NotSupportedException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\RdapApiException;
use RdapApi\Exceptions\SubscriptionRequiredException;
use RdapApi\Exceptions\TemporarilyUnavailableException;
use RdapApi\Exceptions\UpstreamException;
use RdapApi\Exceptions\ValidationException;
use RdapApi\RdapApi;
use RdapApi\Responses\AsnResponse;
use RdapApi\Responses\BulkDomainResponse;
use RdapApi\Responses\DomainResponse;
use RdapApi\Responses\EntityResponse;
use RdapApi\Responses\IpResponse;
use RdapApi\Responses\NameserverResponse;
use RdapApi\Responses\TldListResponse;
use RdapApi\Responses\TldResponse;
use RdapApi\Tests\Fixtures;
use RdapApi\Version;

/**
 * @param  list<Response>  $responses
 * @param  array<int, array<string, mixed>>  $history
 */
function mockClient(array $responses, array &$history = []): RdapApi
{
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    return new RdapApi('test-key', [
        'base_url' => 'https://rdapapi.io/api/v1',
        'handler' => $stack,
    ]);
}

// === Constructor ===

it('throws if API key is empty', function () {
    new RdapApi('');
})->throws(InvalidArgumentException::class, 'API key must be a non-empty string.');

it('sends correct authorization header', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::domainResponse())),
    ], $history);

    $client->domain('google.com');

    expect($history[0]['request']->getHeaderLine('Authorization'))
        ->toBe('Bearer test-key');
});

it('sends correct user agent header', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::domainResponse())),
    ], $history);

    $client->domain('google.com');

    expect($history[0]['request']->getHeaderLine('User-Agent'))
        ->toBe('rdapapi-php/'.Version::SDK);
});

it('sends accept json header', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::domainResponse())),
    ], $history);

    $client->domain('google.com');

    expect($history[0]['request']->getHeaderLine('Accept'))
        ->toBe('application/json');
});

// === Domain ===

it('returns DomainResponse for domain lookup', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::domainResponse())),
    ]);

    $result = $client->domain('google.com');

    expect($result)->toBeInstanceOf(DomainResponse::class)
        ->and($result->domain)->toBe('google.com')
        ->and($result->registrar->name)->toBe('MarkMonitor Inc.')
        ->and($result->dates->registered)->toBe('1997-09-15T04:00:00Z')
        ->and($result->nameservers)->toHaveCount(4)
        ->and($result->meta->cached)->toBeTrue();
});

it('sends follow query param when requested', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::domainFollowResponse())),
    ], $history);

    $result = $client->domain('google.com', ['follow' => true]);

    expect($history[0]['request']->getUri()->getQuery())->toContain('follow=true')
        ->and($result->meta->followed)->toBeTrue();
});

it('does not send follow query param by default', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::domainResponse())),
    ], $history);

    $client->domain('google.com');

    expect($history[0]['request']->getUri()->getQuery())->not->toContain('follow');
});

// === IP ===

it('returns IpResponse for IP lookup', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::ipResponse())),
    ]);

    $result = $client->ip('8.8.8.8');

    expect($result)->toBeInstanceOf(IpResponse::class)
        ->and($result->handle)->toBe('NET-8-8-8-0-1')
        ->and($result->name)->toBe('LVLT-GOGL-8-8-8')
        ->and($result->ip_version)->toBe('v4')
        ->and($result->country)->toBe('US')
        ->and($result->cidr)->toBe(['8.8.8.0/24']);
});

it('sends correct IP path', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::ipResponse())),
    ], $history);

    $client->ip('8.8.8.8');

    expect((string) $history[0]['request']->getUri())->toContain('/ip/8.8.8.8');
});

// === ASN ===

it('returns AsnResponse for ASN lookup', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::asnResponse())),
    ]);

    $result = $client->asn('15169');

    expect($result)->toBeInstanceOf(AsnResponse::class)
        ->and($result->handle)->toBe('AS15169')
        ->and($result->name)->toBe('GOOGLE')
        ->and($result->start_autnum)->toBe(15169);
});

it('strips AS prefix from ASN string', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::asnResponse())),
    ], $history);

    $client->asn('AS15169');

    expect((string) $history[0]['request']->getUri())->toContain('/asn/15169');
});

it('strips lowercase as prefix from ASN', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::asnResponse())),
    ], $history);

    $client->asn('as15169');

    expect((string) $history[0]['request']->getUri())->toContain('/asn/15169');
});

it('accepts integer ASN', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::asnResponse())),
    ], $history);

    $client->asn(15169);

    expect((string) $history[0]['request']->getUri())->toContain('/asn/15169');
});

// === Nameserver ===

it('returns NameserverResponse for nameserver lookup', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::nameserverResponse())),
    ]);

    $result = $client->nameserver('ns1.google.com');

    expect($result)->toBeInstanceOf(NameserverResponse::class)
        ->and($result->ldh_name)->toBe('ns1.google.com')
        ->and($result->ip_addresses->v4)->toBe(['216.239.32.10']);
});

// === Entity ===

it('returns EntityResponse for entity lookup', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::entityResponse())),
    ]);

    $result = $client->entity('GOGL');

    expect($result)->toBeInstanceOf(EntityResponse::class)
        ->and($result->handle)->toBe('GOGL')
        ->and($result->name)->toBe('Google LLC')
        ->and($result->autnums)->toHaveCount(1)
        ->and($result->networks)->toHaveCount(1);
});

// === Bulk Domains ===

it('returns BulkDomainResponse with meta merged', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::bulkResponse())),
    ]);

    $result = $client->bulkDomains(['google.com', 'invalid..com']);

    expect($result)->toBeInstanceOf(BulkDomainResponse::class)
        ->and($result->summary->total)->toBe(2)
        ->and($result->summary->successful)->toBe(1)
        ->and($result->summary->failed)->toBe(1)
        ->and($result->results[0]->status)->toBe('success')
        ->and($result->results[0]->data->domain)->toBe('google.com')
        ->and($result->results[0]->data->meta->rdap_server)->toBe('https://rdap.verisign.com/com/v1/')
        ->and($result->results[0]->data->meta->cached)->toBeTrue()
        ->and($result->results[1]->status)->toBe('error')
        ->and($result->results[1]->error)->toBe('invalid_domain')
        ->and($result->results[1]->data)->toBeNull();
});

it('sends POST with follow for bulk domains', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::bulkResponse())),
    ], $history);

    $client->bulkDomains(['google.com'], ['follow' => true]);

    $body = json_decode((string) $history[0]['request']->getBody(), true);
    expect($history[0]['request']->getMethod())->toBe('POST')
        ->and($body['domains'])->toBe(['google.com'])
        ->and($body['follow'])->toBeTrue();
});

it('does not send follow in bulk body by default', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::bulkResponse())),
    ], $history);

    $client->bulkDomains(['google.com']);

    $body = json_decode((string) $history[0]['request']->getBody(), true);
    expect($body)->not->toHaveKey('follow');
});

it('sends content-type json for bulk request', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::bulkResponse())),
    ], $history);

    $client->bulkDomains(['google.com']);

    expect($history[0]['request']->getHeaderLine('Content-Type'))
        ->toContain('application/json');
});

// === Error Handling ===

it('throws ValidationException on 400', function () {
    $client = mockClient([
        new Response(400, [], json_encode(['error' => 'invalid_input', 'message' => 'Invalid domain name'])),
    ]);

    $client->domain('bad!domain');
})->throws(ValidationException::class, 'Invalid domain name');

it('throws AuthenticationException on 401', function () {
    $client = mockClient([
        new Response(401, [], json_encode(['error' => 'unauthenticated', 'message' => 'Invalid API key'])),
    ]);

    $client->domain('test.com');
})->throws(AuthenticationException::class, 'Invalid API key');

it('throws SubscriptionRequiredException on 403', function () {
    $client = mockClient([
        new Response(403, [], json_encode(['error' => 'subscription_required', 'message' => 'No active subscription'])),
    ]);

    $client->domain('test.com');
})->throws(SubscriptionRequiredException::class);

it('throws NotFoundException on 404', function () {
    $client = mockClient([
        new Response(404, [], json_encode(['error' => 'not_found', 'message' => 'No RDAP data found'])),
    ]);

    try {
        $client->domain('nope.example');
        test()->fail('Expected NotFoundException');
    } catch (NotFoundException $e) {
        expect($e)->not->toBeInstanceOf(NotSupportedException::class);
        expect($e->errorCode)->toBe('not_found');
    }
});

it('throws NotSupportedException when 404 error code is not_supported', function () {
    $client = mockClient([
        new Response(404, [], json_encode([
            'error' => 'not_supported',
            'message' => "The TLD '.nope' is not supported.",
        ])),
    ]);

    try {
        $client->domain('example.nope');
        test()->fail('Expected NotSupportedException');
    } catch (NotSupportedException $e) {
        // Backwards compatible: NotSupportedException extends NotFoundException.
        expect($e)->toBeInstanceOf(NotFoundException::class)
            ->and($e->errorCode)->toBe('not_supported')
            ->and($e->statusCode)->toBe(404);
    }
});

it('routes not_supported errors for IP lookups too', function () {
    $client = mockClient([
        new Response(404, [], json_encode([
            'error' => 'not_supported',
            'message' => 'No RIR covers this IP range.',
        ])),
    ]);

    $client->ip('203.0.113.1');
})->throws(NotSupportedException::class);

it('throws RateLimitException on 429 with retryAfter', function () {
    $client = mockClient([
        new Response(429, ['Retry-After' => '60'], json_encode([
            'error' => 'rate_limited',
            'message' => 'Rate limit exceeded',
        ])),
    ]);

    try {
        $client->domain('test.com');
        test()->fail('Expected RateLimitException');
    } catch (RateLimitException $e) {
        expect($e->statusCode)->toBe(429)
            ->and($e->errorCode)->toBe('rate_limited')
            ->and($e->retryAfter)->toBe(60);
    }
});

it('throws RateLimitException with null retryAfter when header missing', function () {
    $client = mockClient([
        new Response(429, [], json_encode([
            'error' => 'rate_limited',
            'message' => 'Rate limit exceeded',
        ])),
    ]);

    try {
        $client->domain('test.com');
        test()->fail('Expected RateLimitException');
    } catch (RateLimitException $e) {
        expect($e->retryAfter)->toBeNull();
    }
});

it('throws TemporarilyUnavailableException on 503 with retryAfter', function () {
    $client = mockClient([
        new Response(503, ['Retry-After' => '300'], json_encode([
            'error' => 'temporarily_unavailable',
            'message' => 'Data for this domain is temporarily unavailable.',
        ])),
    ]);

    try {
        $client->domain('test.com');
        test()->fail('Expected TemporarilyUnavailableException');
    } catch (TemporarilyUnavailableException $e) {
        expect($e->statusCode)->toBe(503)
            ->and($e->errorCode)->toBe('temporarily_unavailable')
            ->and($e->retryAfter)->toBe(300);
    }
});

it('throws TemporarilyUnavailableException with null retryAfter when header missing', function () {
    $client = mockClient([
        new Response(503, [], json_encode([
            'error' => 'temporarily_unavailable',
            'message' => 'Data for this domain is temporarily unavailable.',
        ])),
    ]);

    try {
        $client->domain('test.com');
        test()->fail('Expected TemporarilyUnavailableException');
    } catch (TemporarilyUnavailableException $e) {
        expect($e->retryAfter)->toBeNull();
    }
});

it('throws UpstreamException on 502', function () {
    $client = mockClient([
        new Response(502, [], json_encode(['error' => 'upstream_error', 'message' => 'Upstream RDAP server failed'])),
    ]);

    $client->domain('test.com');
})->throws(UpstreamException::class);

it('throws RdapApiException for unknown status codes', function () {
    $client = mockClient([
        new Response(500, [], json_encode(['error' => 'server_error', 'message' => 'Internal error'])),
    ]);

    try {
        $client->domain('test.com');
        test()->fail('Expected RdapApiException');
    } catch (RdapApiException $e) {
        expect($e->statusCode)->toBe(500)
            ->and($e->errorCode)->toBe('server_error')
            ->and($e->getMessage())->toBe('Internal error');
    }
});

it('handles invalid JSON in error response', function () {
    $client = mockClient([
        new Response(400, [], 'not json at all'),
    ]);

    try {
        $client->domain('test.com');
        test()->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->errorCode)->toBe('unknown_error')
            ->and($e->getMessage())->toBe('HTTP 400');
    }
});

it('throws error on POST endpoint', function () {
    $client = mockClient([
        new Response(401, [], json_encode(['error' => 'unauthenticated', 'message' => 'Invalid API key'])),
    ]);

    $client->bulkDomains(['test.com']);
})->throws(AuthenticationException::class);

// === TLDs ===

it('lists TLDs with meta and etag', function () {
    $client = mockClient([
        new Response(200, ['ETag' => '"abc"'], json_encode(Fixtures::tldsResponse())),
    ]);

    $result = $client->tlds();

    expect($result)->toBeInstanceOf(TldListResponse::class)
        ->and($result->meta->count)->toBe(2)
        ->and($result->meta->coverage)->toBe(0.5)
        ->and($result->meta->thresholds->always)->toBe(0.99)
        ->and($result->data[0]->tld)->toBe('com')
        ->and($result->data[0]->field_availability?->registered_at)->toBe('always')
        ->and($result->data[1]->field_availability)->toBeNull()
        ->and($result->etag)->toBe('"abc"');
});

it('forwards since and server query params on tlds()', function () {
    $history = [];
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::tldsResponse())),
    ], $history);

    $client->tlds(['since' => '2026-04-01T00:00:00Z', 'server' => 'rdap.verisign.com']);

    $uri = (string) $history[0]['request']->getUri();
    expect($uri)->toContain('since=')
        ->and($uri)->toContain('server=rdap.verisign.com');
});

it('returns null on 304 from tlds()', function () {
    $client = mockClient([
        new Response(304, [], ''),
    ]);

    expect($client->tlds(['if_none_match' => '"abc"']))->toBeNull();
});

it('sends If-None-Match header on tlds() when requested', function () {
    $history = [];
    $client = mockClient([
        new Response(304, [], ''),
    ], $history);

    $client->tlds(['if_none_match' => '"etag-value"']);

    expect($history[0]['request']->getHeaderLine('If-None-Match'))->toBe('"etag-value"');
});

it('returns null etag on tlds() when header missing', function () {
    $client = mockClient([
        new Response(200, [], json_encode(Fixtures::tldsResponse())),
    ]);

    expect($client->tlds()?->etag)->toBeNull();
});

it('still raises typed errors on tlds()', function () {
    $client = mockClient([
        new Response(401, [], json_encode(['error' => 'unauthenticated', 'message' => 'Invalid API token.'])),
    ]);

    $client->tlds();
})->throws(AuthenticationException::class);

it('returns a single TLD with etag', function () {
    $client = mockClient([
        new Response(200, ['ETag' => '"com-1"'], json_encode(Fixtures::tldResponse())),
    ]);

    $result = $client->tld('com');

    expect($result)->toBeInstanceOf(TldResponse::class)
        ->and($result->data->tld)->toBe('com')
        ->and($result->meta->thresholds->usually)->toBe(0.8)
        ->and($result->etag)->toBe('"com-1"');
});

it('returns null on 304 from tld()', function () {
    $client = mockClient([
        new Response(304, [], ''),
    ]);

    expect($client->tld('com', ['if_none_match' => '"com-1"']))->toBeNull();
});

it('throws NotFoundException when tld() target does not exist', function () {
    $client = mockClient([
        new Response(404, [], json_encode([
            'error' => 'not_found',
            'message' => "No RDAP server is registered for the TLD 'nope'.",
        ])),
    ]);

    $client->tld('nope');
})->throws(NotFoundException::class);

// === Version ===

it('has a version string', function () {
    expect(Version::SDK)->toBeString()
        ->and(Version::SDK)->not->toBeEmpty();
});
