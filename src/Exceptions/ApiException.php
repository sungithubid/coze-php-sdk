<?php

declare(strict_types=1);

namespace Coze\Exceptions;

/**
 * API error exception
 */
class ApiException extends CozeException
{
    /** @var int */
    private $errorCode;

    /** @var string */
    private $errorMessage;

    /** @var string|null */
    private $logId;

    public function __construct(int $errorCode, string $errorMessage, ?string $logId = null)
    {
        parent::__construct($errorMessage, $errorCode);
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->logId = $logId;
    }

    /**
     * Get the API error code
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Get the API error message
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get the log ID for debugging
     */
    public function getLogId(): ?string
    {
        return $this->logId;
    }
}
