<?php

declare(strict_types=1);

namespace RdapApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use RdapApi\Exceptions\AuthenticationException;
use RdapApi\Exceptions\NotFoundException;
use RdapApi\Exceptions\RateLimitException;
use RdapApi\Exceptions\RdapApiException;
use RdapApi\Exceptions\SubscriptionRequiredException;
use RdapApi\Exceptions\TemporarilyUnavailableException;
use RdapApi\Exceptions\UpstreamException;
use RdapApi\Exceptions\ValidationException;
use RdapApi\Responses\AsnResponse;
use RdapApi\Responses\BulkDomainResponse;
use RdapApi\Responses\DomainResponse;
use RdapApi\Responses\EntityResponse;
use RdapApi\Responses\IpResponse;
use RdapApi\Responses\NameserverResponse;

final class RdapApi
{
    private const DEFAULT_BASE_URL = 'https://rdapapi.io/api/v1';

    private const DEFAULT_TIMEOUT = 30;

    /** @var array<int, class-string<RdapApiException>> */
    private const ERROR_MAP = [
        400 => ValidationException::class,
        401 => AuthenticationException::class,
        403 => SubscriptionRequiredException::class,
        404 => NotFoundException::class,
        429 => RateLimitException::class,
        502 => UpstreamException::class,
        503 => TemporarilyUnavailableException::class,
    ];

    private Client $client;

    /**
     * @param  array{base_url?: string, timeout?: int, handler?: mixed}  $options
     */
    public function __construct(
        string $apiKey,
        array $options = [],
    ) {
        if ($apiKey === '') {
            throw new \InvalidArgumentException('API key must be a non-empty string.');
        }

        $config = [
            'base_uri' => $options['base_url'] ?? self::DEFAULT_BASE_URL,
            RequestOptions::TIMEOUT => $options['timeout'] ?? self::DEFAULT_TIMEOUT,
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$apiKey,
                'User-Agent' => 'rdapapi-php/'.Version::SDK,
                'Accept' => 'application/json',
            ],
        ];

        if (isset($options['handler'])) {
            $config['handler'] = $options['handler'];
        }

        $this->client = new Client($config);
    }

    /**
     * Look up RDAP registration data for a domain name.
     *
     * @param  array{follow?: bool}  $options
     */
    public function domain(string $name, array $options = []): DomainResponse
    {
        $query = [];
        if (! empty($options['follow'])) {
            $query['follow'] = 'true';
        }

        $data = $this->get("/domain/{$name}", $query);

        return DomainResponse::fromArray($data);
    }

    /**
     * Look up RDAP registration data for an IP address.
     */
    public function ip(string $address): IpResponse
    {
        $data = $this->get("/ip/{$address}");

        return IpResponse::fromArray($data);
    }

    /**
     * Look up RDAP registration data for an ASN.
     *
     * Accepts an integer (15169) or string ("AS15169" or "15169").
     */
    public function asn(int|string $number): AsnResponse
    {
        $value = preg_replace('/^AS/i', '', (string) $number);

        $data = $this->get("/asn/{$value}");

        return AsnResponse::fromArray($data);
    }

    /**
     * Look up RDAP registration data for a nameserver.
     */
    public function nameserver(string $host): NameserverResponse
    {
        $data = $this->get("/nameserver/{$host}");

        return NameserverResponse::fromArray($data);
    }

    /**
     * Look up RDAP registration data for an entity by handle.
     */
    public function entity(string $handle): EntityResponse
    {
        $data = $this->get("/entity/{$handle}");

        return EntityResponse::fromArray($data);
    }

    /**
     * Look up multiple domains in a single request.
     *
     * Requires a Pro or Business plan. Up to 10 domains per call.
     *
     * @param  list<string>  $domains
     * @param  array{follow?: bool}  $options
     */
    public function bulkDomains(array $domains, array $options = []): BulkDomainResponse
    {
        /** @var array<string, mixed> $body */
        $body = ['domains' => $domains];
        if (! empty($options['follow'])) {
            $body['follow'] = true;
        }

        $data = $this->post('/domains/bulk', $body);

        // Merge meta from result level into data for each successful result.
        foreach ($data['results'] ?? [] as $i => $result) {
            if (($result['status'] ?? '') === 'success'
                && isset($result['data'], $result['meta'])) {
                $data['results'][$i]['data']['meta'] = $result['meta'];
                unset($data['results'][$i]['meta']);
            }
        }

        return BulkDomainResponse::fromArray($data);
    }

    /**
     * @param  array<string, string>  $query
     * @return array<string, mixed>
     *
     * @throws RdapApiException
     */
    private function get(string $path, array $query = []): array
    {
        $options = [];
        if ($query !== []) {
            $options[RequestOptions::QUERY] = $query;
        }

        try {
            $response = $this->client->get($path, $options);
        } catch (ClientException|ServerException $e) {
            $this->handleErrorResponse($e);
        }

        /** @var array<string, mixed> */
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     *
     * @throws RdapApiException
     */
    private function post(string $path, array $body): array
    {
        try {
            $response = $this->client->post($path, [
                RequestOptions::JSON => $body,
            ]);
        } catch (ClientException|ServerException $e) {
            $this->handleErrorResponse($e);
        }

        /** @var array<string, mixed> */
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws RdapApiException
     */
    private function handleErrorResponse(ClientException|ServerException $exception): never
    {
        $response = $exception->getResponse();
        $statusCode = $response->getStatusCode();

        try {
            /** @var array{error?: string, message?: string} $body */
            $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $body = [];
        }

        $error = $body['error'] ?? 'unknown_error';
        $message = $body['message'] ?? "HTTP {$statusCode}";

        $retryAfter = null;
        if ($statusCode === 429 || $statusCode === 503) {
            $retryHeader = $response->getHeaderLine('Retry-After');
            if ($retryHeader !== '') {
                $retryAfter = (int) $retryHeader;
            }
        }

        $exceptionClass = self::ERROR_MAP[$statusCode] ?? RdapApiException::class;

        if ($exceptionClass === RateLimitException::class) {
            throw new RateLimitException($message, $statusCode, $error, $retryAfter);
        }

        if ($exceptionClass === TemporarilyUnavailableException::class) {
            throw new TemporarilyUnavailableException($message, $statusCode, $error, $retryAfter);
        }

        throw new $exceptionClass($message, $statusCode, $error);
    }
}
