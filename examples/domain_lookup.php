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
echo 'DNSSEC: '.($domain->dnssec ? 'yes' : 'no')."\n";

// With registrar follow-through.
$followed = $api->domain('google.com', ['follow' => true]);

echo "\n--- With follow ---\n";
echo 'Followed: '.($followed->meta->followed ? 'yes' : 'no')."\n";
if ($followed->entities->registrant?->name) {
    echo "Registrant: {$followed->entities->registrant->name}\n";
}
