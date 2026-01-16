<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Chat response model
 */
class Chat
{
    /** @var string */
    public $id;

    /** @var string */
    public $conversationId;

    /** @var string */
    public $botId;

    /** @var string|null */
    public $status;

    /** @var int|null */
    public $createdAt;

    /** @var int|null */
    public $completedAt;

    /** @var int|null */
    public $failedAt;

    /** @var Usage|null */
    public $usage;

    /** @var string|null */
    public $lastError;

    /** @var array|null */
    public $requiredAction;

    public function __construct(
        string $id,
        string $conversationId,
        string $botId,
        ?string $status = null,
        ?int $createdAt = null,
        ?int $completedAt = null,
        ?int $failedAt = null,
        ?Usage $usage = null,
        ?string $lastError = null,
        ?array $requiredAction = null
    ) {
        $this->id = $id;
        $this->conversationId = $conversationId;
        $this->botId = $botId;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->completedAt = $completedAt;
        $this->failedAt = $failedAt;
        $this->usage = $usage;
        $this->lastError = $lastError;
        $this->requiredAction = $requiredAction;
    }

    /**
     * Create a Chat instance from API response data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['conversation_id'] ?? '',
            $data['bot_id'] ?? '',
            $data['status'] ?? null,
            isset($data['created_at']) ? (int) $data['created_at'] : null,
            isset($data['completed_at']) ? (int) $data['completed_at'] : null,
            isset($data['failed_at']) ? (int) $data['failed_at'] : null,
            isset($data['usage']) ? Usage::fromArray($data['usage']) : null,
            isset($data['last_error']) ? json_encode($data['last_error']) : null,
            $data['required_action'] ?? null
        );
    }

    /**
     * Check if chat is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if chat is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if chat requires action
     */
    public function requiresAction(): bool
    {
        return $this->status === 'requires_action';
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'conversation_id' => $this->conversationId,
            'bot_id' => $this->botId,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'completed_at' => $this->completedAt,
            'failed_at' => $this->failedAt,
            'usage' => $this->usage ? $this->usage->toArray() : null,
            'last_error' => $this->lastError,
            'required_action' => $this->requiredAction,
        ];

        return array_filter($result, function ($v) {
            return $v !== null; });
    }
}
