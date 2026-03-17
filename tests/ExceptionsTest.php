<?php

declare(strict_types=1);

use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\RdapApiException;
use RdapApi\Exceptions\SubscriptionRequiredException;
use RdapApi\Exceptions\TemporarilyUnavailableException;
use RdapApi\Exceptions\UpstreamException;
use RdapApi\Exceptions\ValidationException;

it('creates RdapApiException with correct properties', function () {
    $e = new RdapApiException('Something failed', 500, 'server_error');

    expect($e->getMessage())->toBe('Something failed')
        ->and($e->statusCode)->toBe(500)
        ->and($e->errorCode)->toBe('server_error')
        ->and($e->getCode())->toBe(500)
        ->and($e)->toBeInstanceOf(\Exception::class);
});

it('creates ValidationException extending RdapApiException', function () {
    $e = new ValidationException('Invalid input', 400, 'invalid_input');

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(400)
        ->and($e->errorCode)->toBe('invalid_input');
});

it('creates AuthenticationException extending RdapApiException', function () {
    $e = new AuthenticationException('Bad key', 401, 'unauthenticated');

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(401);
});

it('creates SubscriptionRequiredException extending RdapApiException', function () {
    $e = new SubscriptionRequiredException('No plan', 403, 'subscription_required');

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(403);
});

it('creates NotFoundException extending RdapApiException', function () {
    $e = new NotFoundException('Not found', 404, 'not_found');

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(404);
});

it('creates RateLimitException with retryAfter', function () {
    $e = new RateLimitException('Too fast', 429, 'rate_limited', 60);

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(429)
        ->and($e->retryAfter)->toBe(60);
});

it('creates RateLimitException with null retryAfter', function () {
    $e = new RateLimitException('Too fast', 429, 'rate_limited');

    expect($e->retryAfter)->toBeNull();
});

it('creates TemporarilyUnavailableException with retryAfter', function () {
    $e = new TemporarilyUnavailableException('Unavailable', 503, 'temporarily_unavailable', 300);

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(503)
        ->and($e->retryAfter)->toBe(300);
});

it('creates TemporarilyUnavailableException with null retryAfter', function () {
    $e = new TemporarilyUnavailableException('Unavailable', 503, 'temporarily_unavailable');

    expect($e->retryAfter)->toBeNull();
});

it('creates UpstreamException extending RdapApiException', function () {
    $e = new UpstreamException('Upstream fail', 502, 'upstream_error');

    expect($e)->toBeInstanceOf(RdapApiException::class)
        ->and($e->statusCode)->toBe(502);
});

it('preserves previous exception', function () {
    $previous = new \RuntimeException('original');
    $e = new RdapApiException('wrapped', 500, 'server_error', $previous);

    expect($e->getPrevious())->toBe($previous);
});
