<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

$resp = $api->bulkDomains(
    ['google.com', 'github.com', 'example.com'],
    ['follow' => true],
);

echo "Total: {$resp->summary->total}, Success: {$resp->summary->successful}, Failed: {$resp->summary->failed}\n\n";

foreach ($resp->results as $result) {
    if ($result->status === 'success' && $result->data !== null) {
        $registrar = $result->data->registrar->name ?? 'unknown';
        echo "  {$result->domain} — {$registrar}\n";
    } else {
        $msg = $result->message ?? 'unknown error';
        echo "  {$result->domain} — error: {$msg}\n";
    }
}
