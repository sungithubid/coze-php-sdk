<?php

declare(strict_types=1);

namespace Coze\Auth;

/**
 * Personal Access Token authentication
 */
class TokenAuth implements AuthInterface
{
    /** @var string */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the access token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get authorization headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }
}
