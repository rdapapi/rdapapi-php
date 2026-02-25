<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use RdapApi\RdapApi;

$api = new RdapApi(getenv('RDAPAPI_KEY') ?: '');

$entity = $api->entity('GOGL');

echo "Handle: {$entity->handle}\n";
echo "Organization: {$entity->organization}\n";

if ($entity->email) {
    echo "Email: {$entity->email}\n";
}
if ($entity->phone) {
    echo "Phone: {$entity->phone}\n";
}

echo 'Networks: '.count($entity->networks)."\n";
echo 'Autnums: '.count($entity->autnums)."\n";
