<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

// Basic domain lookup.
$domain = $api->domain('google.com');

echo "Domain: {$domain->domain}\n";
echo "Registrar: {$domain->registrar->name}\n";
echo "Registered: {$domain->dates->registered}\n";
echo "Expires: {$domain->dates->expires}\n";
echo 'Status: '.implode(', ', $domain->status)."\n";
echo 'Nameservers: '.implode(', ', $domain->nameservers)."\n";
echo 'DNSSEC: '.match ($domain->dnssec) {
    true => 'yes',
    false => 'no',
    null => 'not published by the registry',
}."\n";
echo "Answered by {$domain->meta->server} over {$domain->meta->source}\n";

if ($domain->redacted !== null) {
    echo "The registry declared it withheld some fields.\n";
}

// Refuse the WHOIS fallback: a TLD with no RDAP server then raises NotSupportedException.
$rdapOnly = $api->domain('google.com', ['whois' => false]);

echo "\n--- RDAP only ---\n";
echo "Source: {$rdapOnly->meta->source}\n";

// With registrar follow-through.
$followed = $api->domain('google.com', ['follow' => true]);

echo "\n--- With follow ---\n";
echo 'Followed: '.($followed->meta->followed ? 'yes' : 'no')."\n";
if ($followed->entities->registrant?->name) {
    echo "Registrant: {$followed->entities->registrant->name}\n";
}
