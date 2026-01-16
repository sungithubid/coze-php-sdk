<?php

declare(strict_types=1);

namespace Coze;

use Coze\Auth\AuthInterface;
use Coze\Chat\ChatClient;
use Coze\Datasets\DatasetsClient;
use Coze\Http\HttpClient;

/**
 * Main Coze API client
 */
class CozeClient
{
    /**
     * Default API base URL for China
     */
    public const BASE_URL_CN = 'https://api.coze.cn';

    /**
     * Default API base URL for Global
     */
    public const BASE_URL_COM = 'https://api.coze.com';

    /**
     * HTTP client
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Chat API client
     * @var ChatClient
     */
    public $chat;

    /**
     * Datasets API client (Knowledge Base)
     * @var DatasetsClient
     */
    public $datasets;

    /**
     * Create a new Coze client
     *
     * @param AuthInterface $auth Authentication handler
     * @param string $baseUrl API base URL (default: api.coze.cn)
     * @param array $options Additional HTTP client options
     */
    public function __construct(
        AuthInterface $auth,
        string $baseUrl = self::BASE_URL_CN,
        array $options = []
    ) {
        $this->httpClient = new HttpClient($auth, $baseUrl, $options);

        // Initialize API clients
        $this->chat = new ChatClient($this->httpClient);
        $this->datasets = new DatasetsClient($this->httpClient);
    }

    /**
     * Get the HTTP client
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Get the API base URL
     */
    public function getBaseUrl(): string
    {
        return $this->httpClient->getBaseUrl();
    }
}
