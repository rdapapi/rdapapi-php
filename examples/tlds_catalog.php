<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

// Full catalog. Does not count against your monthly quota.
$tlds = $api->tlds();
if ($tlds !== null) {
    printf("%d TLDs supported, coverage %d%%\n", $tlds->meta->count, (int) round($tlds->meta->coverage * 100));

    foreach (array_slice($tlds->data, 0, 5) as $tld) {
        $availability = $tld->field_availability;
        if ($availability === null) {
            echo ".{$tld->tld} via {$tld->rdap_server_host} (not enough data yet)\n";
        } else {
            echo ".{$tld->tld} via {$tld->rdap_server_host}: registrar={$availability->registrar}, expires_at={$availability->expires_at}\n";
        }
    }

    // Skip the transfer when nothing has changed.
    $later = $api->tlds(['if_none_match' => $tlds->etag ?? '']);
    echo $later === null ? "No change since last poll\n" : "Changed\n";
}

// Single-TLD lookup.
$com = $api->tld('com');
if ($com !== null) {
    echo ".com supported since {$com->data->supported_since}\n";
}
