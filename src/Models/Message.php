<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Message model for chat
 */
class Message
{
    /** @var string */
    public $role;

    /** @var string */
    public $type;

    /** @var string */
    public $content;

    /** @var string */
    public $contentType;

    /** @var string|null */
    public $id;

    /** @var string|null */
    public $conversationId;

    /** @var string|null */
    public $botId;

    /** @var string|null */
    public $chatId;

    /** @var array|null */
    public $metaData;

    /** @var int|null */
    public $createdAt;

    /** @var int|null */
    public $updatedAt;

    public function __construct(
        string $role,
        string $type,
        string $content,
        string $contentType = 'text',
        ?string $id = null,
        ?string $conversationId = null,
        ?string $botId = null,
        ?string $chatId = null,
        ?array $metaData = null,
        ?int $createdAt = null,
        ?int $updatedAt = null
    ) {
        $this->role = $role;
        $this->type = $type;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->id = $id;
        $this->conversationId = $conversationId;
        $this->botId = $botId;
        $this->chatId = $chatId;
        $this->metaData = $metaData;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Build a user question message with text content
     */
    public static function buildUserQuestionText(string $content): array
    {
        return [
            'role' => 'user',
            'type' => 'question',
            'content_type' => 'text',
            'content' => $content,
        ];
    }

    /**
     * Build an assistant answer message
     */
    public static function buildAssistantAnswer(string $content): array
    {
        return [
            'role' => 'assistant',
            'type' => 'answer',
            'content_type' => 'text',
            'content' => $content,
        ];
    }

    /**
     * Build a user question message with object list content (for multimodal)
     *
     * @param array $contentObjects Array of content objects
     */
    public static function buildUserQuestionObjects(array $contentObjects): array
    {
        return [
            'role' => 'user',
            'type' => 'question',
            'content_type' => 'object_string',
            'content' => json_encode($contentObjects),
        ];
    }

    /**
     * Create a text message object for multimodal messages
     */
    public static function textObject(string $text): array
    {
        return [
            'type' => 'text',
            'text' => $text,
        ];
    }

    /**
     * Create an image message object by file ID
     */
    public static function imageObjectById(string $fileId): array
    {
        return [
            'type' => 'image',
            'file_id' => $fileId,
        ];
    }

    /**
     * Create an image message object by URL
     */
    public static function imageObjectByUrl(string $url): array
    {
        return [
            'type' => 'image',
            'file_url' => $url,
        ];
    }

    /**
     * Create a Message instance from API response data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['role'] ?? '',
            $data['type'] ?? '',
            $data['content'] ?? '',
            $data['content_type'] ?? 'text',
            $data['id'] ?? null,
            $data['conversation_id'] ?? null,
            $data['bot_id'] ?? null,
            $data['chat_id'] ?? null,
            $data['meta_data'] ?? null,
            isset($data['created_at']) ? (int) $data['created_at'] : null,
            isset($data['updated_at']) ? (int) $data['updated_at'] : null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'role' => $this->role,
            'type' => $this->type,
            'content' => $this->content,
            'content_type' => $this->contentType,
            'conversation_id' => $this->conversationId,
            'bot_id' => $this->botId,
            'chat_id' => $this->chatId,
            'meta_data' => $this->metaData,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ], function ($v) {
            return $v !== null; });
    }
}
