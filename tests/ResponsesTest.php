<?php

declare(strict_types=1);

use RdapApi\Responses\AsnResponse;
use RdapApi\Responses\BulkDomainResponse;
use RdapApi\Responses\Contact;
use RdapApi\Responses\Dates;
use RdapApi\Responses\DomainResponse;
use RdapApi\Responses\Entities;
use RdapApi\Responses\EntityResponse;
use RdapApi\Responses\IpAddresses;
use RdapApi\Responses\IpResponse;
use RdapApi\Responses\Meta;
use RdapApi\Responses\NameserverResponse;
use RdapApi\Responses\Registrar;
use RdapApi\Responses\Remark;
use RdapApi\Tests\Fixtures;

it('parses DomainResponse from array', function () {
    $resp = DomainResponse::fromArray(Fixtures::domainResponse());

    expect($resp->domain)->toBe('google.com')
        ->and($resp->unicode_name)->toBe('google.com')
        ->and($resp->handle)->toBe('D-123')
        ->and($resp->status)->toBe(['client transfer prohibited', 'server delete prohibited'])
        ->and($resp->registrar->name)->toBe('MarkMonitor Inc.')
        ->and($resp->registrar->iana_id)->toBe('292')
        ->and($resp->registrar->abuse_email)->toBe('abusecomplaints@markmonitor.com')
        ->and($resp->registrar->abuse_phone)->toBe('+1.2086851750')
        ->and($resp->registrar->url)->toBe('https://www.markmonitor.com')
        ->and($resp->dates->registered)->toBe('1997-09-15T04:00:00Z')
        ->and($resp->dates->expires)->toBe('2028-09-14T04:00:00Z')
        ->and($resp->dates->updated)->toBe('2024-02-20T10:16:08Z')
        ->and($resp->nameservers)->toHaveCount(4)
        ->and($resp->dnssec)->toBeFalse()
        ->and($resp->entities->registrant)->toBeInstanceOf(Contact::class)
        ->and($resp->entities->registrant->name)->toBe('Google LLC')
        ->and($resp->entities->registrant->country_code)->toBe('US')
        ->and($resp->meta->rdap_server)->toBe('https://rdap.markmonitor.com/rdap/')
        ->and($resp->meta->cached)->toBeTrue();
});

it('handles nullable fields in DomainResponse', function () {
    $resp = DomainResponse::fromArray([
        'domain' => 'test.com',
        'status' => [],
        'registrar' => [],
        'dates' => ['registered' => null, 'expires' => null],
        'nameservers' => [],
        'dnssec' => false,
        'entities' => [],
        'meta' => ['rdap_server' => '', 'raw_rdap_url' => '', 'cached' => false, 'cache_expires' => ''],
    ]);

    expect($resp->unicode_name)->toBeNull()
        ->and($resp->handle)->toBeNull()
        ->and($resp->dates->registered)->toBeNull()
        ->and($resp->entities->registrant)->toBeNull()
        ->and($resp->meta->followed)->toBeNull();
});

it('parses IpResponse from array', function () {
    $resp = IpResponse::fromArray(Fixtures::ipResponse());

    expect($resp->handle)->toBe('NET-8-8-8-0-1')
        ->and($resp->name)->toBe('LVLT-GOGL-8-8-8')
        ->and($resp->type)->toBe('ALLOCATION')
        ->and($resp->start_address)->toBe('8.8.8.0')
        ->and($resp->end_address)->toBe('8.8.8.255')
        ->and($resp->ip_version)->toBe('v4')
        ->and($resp->parent_handle)->toBe('NET-8-0-0-0-1')
        ->and($resp->country)->toBe('US')
        ->and($resp->cidr)->toBe(['8.8.8.0/24'])
        ->and($resp->remarks)->toHaveCount(1)
        ->and($resp->remarks[0])->toBeInstanceOf(Remark::class)
        ->and($resp->remarks[0]->title)->toBe('Note')
        ->and($resp->remarks[0]->description)->toBe('Google Public DNS')
        ->and($resp->port43)->toBe('whois.arin.net')
        ->and($resp->entities->abuse)->toBeInstanceOf(Contact::class)
        ->and($resp->entities->abuse->email)->toBe('network-abuse@google.com');
});

it('parses AsnResponse from array', function () {
    $resp = AsnResponse::fromArray(Fixtures::asnResponse());

    expect($resp->handle)->toBe('AS15169')
        ->and($resp->name)->toBe('GOOGLE')
        ->and($resp->type)->toBe('DIRECT ALLOCATION')
        ->and($resp->start_autnum)->toBe(15169)
        ->and($resp->end_autnum)->toBe(15169)
        ->and($resp->port43)->toBe('whois.arin.net');
});

it('parses NameserverResponse from array', function () {
    $resp = NameserverResponse::fromArray(Fixtures::nameserverResponse());

    expect($resp->ldh_name)->toBe('ns1.google.com')
        ->and($resp->unicode_name)->toBe('ns1.google.com')
        ->and($resp->handle)->toBe('NS-001')
        ->and($resp->ip_addresses)->toBeInstanceOf(IpAddresses::class)
        ->and($resp->ip_addresses->v4)->toBe(['216.239.32.10'])
        ->and($resp->ip_addresses->v6)->toBe(['2001:4860:4802:32::a']);
});

it('parses EntityResponse from array', function () {
    $resp = EntityResponse::fromArray(Fixtures::entityResponse());

    expect($resp->handle)->toBe('GOGL')
        ->and($resp->name)->toBe('Google LLC')
        ->and($resp->organization)->toBe('Google LLC')
        ->and($resp->email)->toBe('arin-contact@google.com')
        ->and($resp->phone)->toBe('+1-650-253-0000')
        ->and($resp->address)->toBe('1600 Amphitheatre Parkway')
        ->and($resp->contact_url)->toBe('https://google.com')
        ->and($resp->country_code)->toBe('US')
        ->and($resp->roles)->toBe(['registrant'])
        ->and($resp->public_ids)->toHaveCount(1)
        ->and($resp->public_ids[0]->type)->toBe('ARIN OrgID')
        ->and($resp->public_ids[0]->identifier)->toBe('GOGL')
        ->and($resp->autnums)->toHaveCount(1)
        ->and($resp->autnums[0]->handle)->toBe('AS15169')
        ->and($resp->autnums[0]->start_autnum)->toBe(15169)
        ->and($resp->networks)->toHaveCount(1)
        ->and($resp->networks[0]->handle)->toBe('NET-8-8-8-0-1')
        ->and($resp->networks[0]->cidr)->toBe(['8.8.8.0/24']);
});

it('parses BulkDomainResponse from array', function () {
    $resp = BulkDomainResponse::fromArray(Fixtures::bulkResponse());

    expect($resp->results)->toHaveCount(2)
        ->and($resp->summary->total)->toBe(2)
        ->and($resp->summary->successful)->toBe(1)
        ->and($resp->summary->failed)->toBe(1)
        ->and($resp->results[0]->domain)->toBe('google.com')
        ->and($resp->results[0]->status)->toBe('success')
        ->and($resp->results[0]->data)->toBeInstanceOf(DomainResponse::class)
        ->and($resp->results[1]->status)->toBe('error')
        ->and($resp->results[1]->error)->toBe('invalid_domain')
        ->and($resp->results[1]->data)->toBeNull();
});

it('parses Meta with follow fields', function () {
    $meta = Meta::fromArray([
        'rdap_server' => 'https://rdap.example.com',
        'raw_rdap_url' => 'https://rdap.example.com/domain/test.com',
        'cached' => false,
        'cache_expires' => '',
        'followed' => true,
        'registrar_rdap_server' => 'https://rdap.registrar.com',
        'follow_error' => null,
    ]);

    expect($meta->followed)->toBeTrue()
        ->and($meta->registrar_rdap_server)->toBe('https://rdap.registrar.com')
        ->and($meta->follow_error)->toBeNull();
});

it('parses Entities with all roles', function () {
    $entities = Entities::fromArray([
        'registrant' => ['name' => 'Reg'],
        'administrative' => ['name' => 'Admin'],
        'technical' => ['name' => 'Tech'],
        'billing' => ['name' => 'Bill'],
        'abuse' => ['name' => 'Abuse'],
    ]);

    expect($entities->registrant->name)->toBe('Reg')
        ->and($entities->administrative->name)->toBe('Admin')
        ->and($entities->technical->name)->toBe('Tech')
        ->and($entities->billing->name)->toBe('Bill')
        ->and($entities->abuse->name)->toBe('Abuse');
});

it('handles empty arrays gracefully', function () {
    $ip = IpAddresses::fromArray([]);
    expect($ip->v4)->toBe([])
        ->and($ip->v6)->toBe([]);

    $dates = Dates::fromArray([]);
    expect($dates->registered)->toBeNull()
        ->and($dates->expires)->toBeNull()
        ->and($dates->updated)->toBeNull();

    $registrar = Registrar::fromArray([]);
    expect($registrar->name)->toBeNull()
        ->and($registrar->iana_id)->toBeNull();
});
