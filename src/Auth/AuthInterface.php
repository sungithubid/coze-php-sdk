<?php

declare(strict_types=1);

namespace Coze\Auth;

/**
 * Authentication interface for Coze API
 */
interface AuthInterface
{
    /**
     * Get the access token
     */
    public function getToken(): string;

    /**
     * Get authorization headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array;
}
