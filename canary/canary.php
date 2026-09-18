<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException;
use RdapApi\Exceptions\RdapApiException;
use RdapApi\RdapApi;
use RdapApi\Responses\Entities;

/**
 * Production canary.
 *
 * The unit suite replays frozen fixtures, so it proves the SDK is
 * self-consistent but never that the API moved underneath it. Every probe below
 * calls production through the SDK's public API and fails when the live
 * contract stops matching what the response classes model.
 *
 * Run it locally the same way CI does, with the key in RDAPAPI_API_KEY:
 *
 *     RDAPAPI_API_KEY=... php canary/canary.php
 *
 * It sits outside tests/ on purpose: it needs the network, so it must never
 * join the offline suite or its coverage gate.
 */
const RETRY_BACKOFF_SECONDS = 5;

/**
 * Call production, retrying once on a transport error or a 5xx.
 *
 * These probes reach real registries, where a blip is normal. A contract
 * assertion is never retried — a field that is wrong is wrong.
 *
 * @template TResponse
 *
 * @param  callable(): TResponse  $call
 * @return TResponse
 */
function retrying(string $probe, callable $call): mixed
{
    try {
        return $call();
    } catch (RdapApiException $exception) {
        if ($exception->statusCode < 500) {
            throw $exception;
        }

        echo "  retry {$probe}: HTTP {$exception->statusCode} from production\n";
    } catch (GuzzleException $exception) {
        echo "  retry {$probe}: transport error: {$exception->getMessage()}\n";
    }

    sleep(RETRY_BACKOFF_SECONDS);

    return $call();
}

/**
 * How many of the five contact roles the answer actually carries.
 */
function filledContacts(Entities $entities): int
{
    return count(array_filter([
        $entities->registrant,
        $entities->administrative,
        $entities->technical,
        $entities->billing,
        $entities->abuse,
    ]));
}

$apiKey = getenv('RDAPAPI_API_KEY') ?: '';

if ($apiKey === '') {
    fwrite(STDERR, "RDAPAPI_API_KEY is not set.\n");

    exit(1);
}

$api = new RdapApi($apiKey);

/** @var list<string> $failures */
$failures = [];

/**
 * Run one probe, and turn an unexpected throw into a reported failure so the
 * remaining probes still get their answer.
 *
 * The `$expect` handed to the probe records one assertion, naming both what was
 * expected and what arrived, so a red run is diagnosable from the log alone.
 *
 * @param  callable(callable(string, bool, string, string): void): void  $probe
 */
$run = function (string $name, callable $probe) use (&$failures): void {
    echo "{$name}\n";

    $expect = function (string $subject, bool $passed, string $expected, string $actual) use (&$failures, $name): void {
        if ($passed) {
            echo "  ok    {$subject}: {$actual}\n";

            return;
        }

        $failure = "{$name}: {$subject} expected {$expected}, got {$actual}";
        $failures[] = $failure;

        echo "  FAIL  {$failure}\n";
    };

    try {
        $probe($expect);
    } catch (Throwable $exception) {
        $failure = "{$name}: threw ".$exception::class.' — '.$exception->getMessage();
        $failures[] = $failure;

        echo "  FAIL  {$failure}\n";
    }

    echo "\n";
};

$run('ping', function (callable $expect) use ($api): void {
    // ping() never throws: an unreachable host is reported as false, so the
    // one allowed retry is on the value rather than on an exception.
    $alive = $api->ping();

    if (! $alive) {
        sleep(RETRY_BACKOFF_SECONDS);
        $alive = $api->ping();
    }

    $expect('liveness', $alive, 'ok', $alive ? 'ok' : 'unreachable or not ok');
});

$run('domain google.com (follow)', function (callable $expect) use ($api): void {
    $domain = retrying('domain google.com', fn () => $api->domain('google.com', ['follow' => true]));

    $expect('meta.source', $domain->meta->source === 'rdap', 'rdap', $domain->meta->source);
    $expect('meta.server', ($domain->meta->server ?? '') !== '', 'a non-empty hostname', $domain->meta->server ?? 'null');
    $expect('registrar.name', ($domain->registrar->name ?? '') !== '', 'a non-empty name', $domain->registrar->name ?? 'null');

    $contacts = filledContacts($domain->entities);
    $expect('entities', $contacts > 0, 'at least one contact role', "{$contacts} contact role(s)");
});

$run('domain google.it (whois fallback)', function (callable $expect) use ($api): void {
    $domain = retrying('domain google.it', fn () => $api->domain('google.it'));

    $expect('meta.source', $domain->meta->source === 'whois', 'whois', $domain->meta->source);
    $expect('meta.server', $domain->meta->server === 'whois.nic.it', 'whois.nic.it', $domain->meta->server ?? 'null');

    // WHOIS leaves both of these out of the payload entirely. Reading them must
    // give null rather than throw: this is the exact shape that crashed the
    // Python SDK in production.
    $expect('meta.rdap_server', $domain->meta->rdap_server === null, 'null', $domain->meta->rdap_server ?? 'null');
    $expect('meta.raw_rdap_url', $domain->meta->raw_rdap_url === null, 'null', $domain->meta->raw_rdap_url ?? 'null');
});

$run('ip 45.83.220.1', function (callable $expect) use ($api): void {
    $ip = retrying('ip 45.83.220.1', fn () => $api->ip('45.83.220.1'));

    // The geofeed is published by the network holder, not by us. Check that
    // they still publish one before reading a red run here as an SDK bug.
    $expect('geofeed', ($ip->geofeed ?? '') !== '', 'a non-empty URL', $ip->geofeed ?? 'null');
});

$run('tld it', function (callable $expect) use ($api): void {
    $tld = retrying('tld it', fn () => $api->tld('it'));

    // Null means HTTP 304, which is impossible without a conditional request.
    $expect('response', $tld !== null, 'a catalog entry', $tld === null ? 'null (HTTP 304)' : 'a catalog entry');

    if ($tld === null) {
        return;
    }

    $expect('protocol', $tld->data->protocol === 'whois', 'whois', $tld->data->protocol);
    $expect('server', $tld->data->server !== '', 'a non-empty hostname', $tld->data->server === '' ? 'empty' : $tld->data->server);

    // Both are deprecated in favour of `server`, and a WHOIS-only TLD has
    // neither. Reading them must give null rather than throw.
    $expect('rdap_server_host', $tld->data->rdap_server_host === null, 'null', $tld->data->rdap_server_host ?? 'null');
    $expect('rdap_server_url', $tld->data->rdap_server_url === null, 'null', $tld->data->rdap_server_url ?? 'null');
});

if ($failures !== []) {
    fwrite(STDERR, count($failures)." canary assertion(s) failed:\n");

    foreach ($failures as $failure) {
        fwrite(STDERR, "  - {$failure}\n");
    }

    exit(1);
}

echo "All canary probes passed.\n";
