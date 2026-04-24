<?php

declare(strict_types=1);

namespace RdapApi\Tests;

final class Fixtures
{
    /**
     * @return array<string, mixed>
     */
    public static function domainResponse(): array
    {
        return [
            'domain' => 'google.com',
            'unicode_name' => 'google.com',
            'handle' => 'D-123',
            'status' => ['client transfer prohibited', 'server delete prohibited'],
            'registrar' => [
                'name' => 'MarkMonitor Inc.',
                'iana_id' => '292',
                'abuse_email' => 'abusecomplaints@markmonitor.com',
                'abuse_phone' => '+1.2086851750',
                'url' => 'https://www.markmonitor.com',
            ],
            'dates' => [
                'registered' => '1997-09-15T04:00:00Z',
                'expires' => '2028-09-14T04:00:00Z',
                'updated' => '2024-02-20T10:16:08Z',
            ],
            'nameservers' => ['ns1.google.com', 'ns2.google.com', 'ns3.google.com', 'ns4.google.com'],
            'dnssec' => false,
            'entities' => [
                'registrant' => [
                    'handle' => 'C-001',
                    'name' => 'Google LLC',
                    'organization' => 'Google LLC',
                    'email' => 'registrar@google.com',
                    'country_code' => 'US',
                ],
            ],
            'meta' => [
                'rdap_server' => 'https://rdap.markmonitor.com/rdap/',
                'raw_rdap_url' => 'https://rdap.markmonitor.com/rdap/domain/google.com',
                'cached' => true,
                'cache_expires' => '2024-08-14T08:00:00Z',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function domainFollowResponse(): array
    {
        $data = self::domainResponse();
        $data['meta']['followed'] = true;
        $data['meta']['registrar_rdap_server'] = 'https://rdap.markmonitor.com/rdap/';

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function ipResponse(): array
    {
        return [
            'handle' => 'NET-8-8-8-0-1',
            'name' => 'LVLT-GOGL-8-8-8',
            'type' => 'ALLOCATION',
            'start_address' => '8.8.8.0',
            'end_address' => '8.8.8.255',
            'ip_version' => 'v4',
            'parent_handle' => 'NET-8-0-0-0-1',
            'country' => 'US',
            'status' => ['active'],
            'dates' => [
                'registered' => '2014-03-14T00:00:00Z',
                'updated' => '2014-03-14T00:00:00Z',
            ],
            'entities' => [
                'abuse' => [
                    'handle' => 'ABUSE-001',
                    'email' => 'network-abuse@google.com',
                ],
            ],
            'cidr' => ['8.8.8.0/24'],
            'remarks' => [
                ['title' => 'Note', 'description' => 'Google Public DNS'],
            ],
            'port43' => 'whois.arin.net',
            'meta' => [
                'rdap_server' => 'https://rdap.arin.net/registry/',
                'raw_rdap_url' => 'https://rdap.arin.net/registry/ip/8.8.8.8',
                'cached' => false,
                'cache_expires' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function asnResponse(): array
    {
        return [
            'handle' => 'AS15169',
            'name' => 'GOOGLE',
            'type' => 'DIRECT ALLOCATION',
            'start_autnum' => 15169,
            'end_autnum' => 15169,
            'status' => ['active'],
            'dates' => [
                'registered' => '2000-03-10T00:00:00Z',
                'updated' => '2012-02-24T00:00:00Z',
            ],
            'entities' => [],
            'remarks' => [],
            'port43' => 'whois.arin.net',
            'meta' => [
                'rdap_server' => 'https://rdap.arin.net/registry/',
                'raw_rdap_url' => 'https://rdap.arin.net/registry/autnum/15169',
                'cached' => false,
                'cache_expires' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function nameserverResponse(): array
    {
        return [
            'ldh_name' => 'ns1.google.com',
            'unicode_name' => 'ns1.google.com',
            'handle' => 'NS-001',
            'ip_addresses' => [
                'v4' => ['216.239.32.10'],
                'v6' => ['2001:4860:4802:32::a'],
            ],
            'status' => ['active'],
            'dates' => [],
            'entities' => [],
            'meta' => [
                'rdap_server' => 'https://rdap.verisign.com/com/v1/',
                'raw_rdap_url' => 'https://rdap.verisign.com/com/v1/nameserver/ns1.google.com',
                'cached' => false,
                'cache_expires' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function entityResponse(): array
    {
        return [
            'handle' => 'GOGL',
            'name' => 'Google LLC',
            'organization' => 'Google LLC',
            'email' => 'arin-contact@google.com',
            'phone' => '+1-650-253-0000',
            'address' => '1600 Amphitheatre Parkway',
            'contact_url' => 'https://google.com',
            'country_code' => 'US',
            'roles' => ['registrant'],
            'status' => ['active'],
            'dates' => [
                'registered' => '2000-03-30T00:00:00Z',
                'updated' => '2024-01-01T00:00:00Z',
            ],
            'remarks' => [
                ['title' => 'Registration', 'description' => 'First registered in 2000'],
            ],
            'port43' => 'whois.arin.net',
            'public_ids' => [
                ['type' => 'ARIN OrgID', 'identifier' => 'GOGL'],
            ],
            'entities' => [],
            'autnums' => [
                ['handle' => 'AS15169', 'name' => 'GOOGLE', 'start_autnum' => 15169, 'end_autnum' => 15169],
            ],
            'networks' => [
                [
                    'handle' => 'NET-8-8-8-0-1',
                    'name' => 'LVLT-GOGL-8-8-8',
                    'start_address' => '8.8.8.0',
                    'end_address' => '8.8.8.255',
                    'ip_version' => 'v4',
                    'cidr' => ['8.8.8.0/24'],
                ],
            ],
            'meta' => [
                'rdap_server' => 'https://rdap.arin.net/registry/',
                'raw_rdap_url' => 'https://rdap.arin.net/registry/entity/GOGL',
                'cached' => false,
                'cache_expires' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function bulkResponse(): array
    {
        return [
            'results' => [
                [
                    'domain' => 'google.com',
                    'status' => 'success',
                    'data' => [
                        'domain' => 'google.com',
                        'status' => ['active'],
                        'registrar' => ['name' => 'MarkMonitor Inc.'],
                        'dates' => [],
                        'nameservers' => [],
                        'dnssec' => false,
                        'entities' => [],
                        'meta' => ['rdap_server' => '', 'raw_rdap_url' => '', 'cached' => false, 'cache_expires' => ''],
                    ],
                    'meta' => [
                        'rdap_server' => 'https://rdap.verisign.com/com/v1/',
                        'raw_rdap_url' => 'https://rdap.verisign.com/com/v1/domain/google.com',
                        'cached' => true,
                        'cache_expires' => '2024-12-01T00:00:00Z',
                    ],
                ],
                [
                    'domain' => 'invalid..com',
                    'status' => 'error',
                    'error' => 'invalid_domain',
                    'message' => 'Invalid domain name',
                ],
            ],
            'summary' => [
                'total' => 2,
                'successful' => 1,
                'failed' => 1,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function tldsResponse(): array
    {
        return [
            'data' => [
                [
                    'tld' => 'com',
                    'supported_since' => '2026-03-07T00:00:00Z',
                    'rdap_server_host' => 'rdap.verisign.com',
                    'rdap_server_url' => 'https://rdap.verisign.com/com/v1/',
                    'field_availability' => [
                        'registrar' => 'sometimes',
                        'registered_at' => 'always',
                        'expires_at' => 'always',
                        'nameservers' => 'always',
                        'status' => 'always',
                    ],
                ],
                [
                    'tld' => 'fr',
                    'supported_since' => '2026-03-07T00:00:00Z',
                    'rdap_server_host' => 'rdap.nic.fr',
                    'rdap_server_url' => 'https://rdap.nic.fr/',
                    'field_availability' => null,
                ],
            ],
            'meta' => [
                'computed_at' => '2026-04-22T10:00:00Z',
                'count' => 2,
                'coverage' => 0.5,
                'thresholds' => ['always' => 0.99, 'usually' => 0.8, 'sometimes' => 0.0],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function tldResponse(): array
    {
        return [
            'data' => [
                'tld' => 'com',
                'supported_since' => '2026-03-07T00:00:00Z',
                'rdap_server_host' => 'rdap.verisign.com',
                'rdap_server_url' => 'https://rdap.verisign.com/com/v1/',
                'field_availability' => [
                    'registrar' => 'sometimes',
                    'registered_at' => 'always',
                    'expires_at' => 'always',
                    'nameservers' => 'always',
                    'status' => 'always',
                ],
            ],
            'meta' => [
                'computed_at' => '2026-04-22T10:00:00Z',
                'thresholds' => ['always' => 0.99, 'usually' => 0.8, 'sometimes' => 0.0],
            ],
        ];
    }
}
