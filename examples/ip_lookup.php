<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

// IP address lookup.
$ip = $api->ip('8.8.8.8');

echo "Name: {$ip->name}\n";
echo "Range: {$ip->start_address} - {$ip->end_address}\n";
echo "Country: {$ip->country}\n";
echo 'CIDR: '.implode(', ', $ip->cidr)."\n";
echo 'Status: '.implode(', ', $ip->status)."\n";

// ASN lookup.
$asn = $api->asn(15169);

echo "\nASN: {$asn->handle}\n";
echo "Name: {$asn->name}\n";

// Nameserver lookup.
$ns = $api->nameserver('ns1.google.com');

echo "\nNameserver: {$ns->ldh_name}\n";
echo 'IPv4: '.implode(', ', $ns->ip_addresses->v4)."\n";
echo 'IPv6: '.implode(', ', $ns->ip_addresses->v6)."\n";
