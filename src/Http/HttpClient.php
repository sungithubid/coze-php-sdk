<?php

declare(strict_types=1);

namespace Coze\Http;

use Coze\Auth\AuthInterface;
use Coze\Exceptions\ApiException;
use Coze\Exceptions\CozeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP client wrapper for Coze API
 */
class HttpClient
{
    /** @var Client */
    private $client;

    /** @var AuthInterface */
    private $auth;

    /** @var string */
    private $baseUrl;

    public function __construct(
        AuthInterface $auth,
        string $baseUrl = 'https://api.coze.cn',
        array $options = []
    ) {
        $this->auth = $auth;
        $this->baseUrl = rtrim($baseUrl, '/');

        $defaultOptions = [
            'base_uri' => $this->baseUrl,
            'timeout' => $options['timeout'] ?? 30,
            'connect_timeout' => $options['connect_timeout'] ?? 10,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        $this->client = new Client(array_merge($defaultOptions, $options));
    }

    /**
     * Send a POST request
     *
     * @param string $path API path
     * @param array $data Request body data
     * @param array $options Additional Guzzle options
     * @return array Decoded response
     * @throws CozeException
     */
    public function post(string $path, array $data = [], array $options = []): array
    {
        try {
            // Merge headers properly - don't let options overwrite auth headers
            $headers = $this->auth->getHeaders();
            if (isset($options['headers'])) {
                $headers = array_merge($headers, $options['headers']);
                unset($options['headers']);
            }

            $response = $this->client->post($path, array_merge([
                RequestOptions::JSON => $data,
                RequestOptions::HEADERS => $headers,
            ], $options));

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw new CozeException('Request failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Send a POST request and return stream for SSE
     *
     * @param string $path API path
     * @param array $data Request body data
     * @param array $options Additional Guzzle options
     * @return StreamInterface Response stream
     * @throws CozeException
     */
    public function postStream(string $path, array $data = [], array $options = []): StreamInterface
    {
        try {
            $response = $this->client->post($path, array_merge([
                RequestOptions::JSON => $data,
                RequestOptions::HEADERS => array_merge($this->auth->getHeaders(), [
                    'Accept' => 'text/event-stream',
                ]),
                RequestOptions::STREAM => true,
            ], $options));

            return $response->getBody();
        } catch (GuzzleException $e) {
            throw new CozeException('Stream request failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Send a GET request
     *
     * @param string $path API path
     * @param array $query Query parameters
     * @param array $options Additional Guzzle options
     * @return array Decoded response
     * @throws CozeException
     */
    public function get(string $path, array $query = [], array $options = []): array
    {
        try {
            $requestOptions = [
                RequestOptions::HEADERS => $this->auth->getHeaders(),
            ];
            if (!empty($query)) {
                $requestOptions[RequestOptions::QUERY] = $query;
            }

            $response = $this->client->get($path, array_merge($requestOptions, $options));

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw new CozeException('Request failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Send a PUT request
     *
     * @param string $path API path
     * @param array $data Request body data
     * @param array $options Additional Guzzle options
     * @return array Decoded response
     * @throws CozeException
     */
    public function put(string $path, array $data = [], array $options = []): array
    {
        try {
            $response = $this->client->put($path, array_merge([
                RequestOptions::JSON => $data,
                RequestOptions::HEADERS => $this->auth->getHeaders(),
            ], $options));

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw new CozeException('Request failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Send a DELETE request
     *
     * @param string $path API path
     * @param array $options Additional Guzzle options
     * @return array Decoded response
     * @throws CozeException
     */
    public function delete(string $path, array $options = []): array
    {
        try {
            $response = $this->client->delete($path, array_merge([
                RequestOptions::HEADERS => $this->auth->getHeaders(),
            ], $options));

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw new CozeException('Request failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Handle API response
     *
     * @param ResponseInterface $response
     * @return array
     * @throws ApiException
     * @throws CozeException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CozeException('Failed to decode response: ' . json_last_error_msg());
        }

        // Check for API errors
        if (isset($data['code']) && $data['code'] !== 0) {
            $logId = $response->getHeaderLine('X-Tt-Logid') ?: null;
            throw new ApiException(
                $data['code'],
                $data['msg'] ?? 'Unknown error',
                $logId
            );
        }

        return $data;
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
