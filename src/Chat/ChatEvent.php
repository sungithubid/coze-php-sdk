<?php

declare(strict_types=1);

namespace Coze\Chat;

use Coze\Models\Chat;
use Coze\Models\Message;

/**
 * Chat event for SSE streaming
 */
class ChatEvent
{
    /** @var string */
    public $event;

    /** @var Message|null */
    public $message;

    /** @var Chat|null */
    public $chat;

    /** @var array|null */
    public $rawData;

    public function __construct(
        string $event,
        ?Message $message = null,
        ?Chat $chat = null,
        ?array $rawData = null
    ) {
        $this->event = $event;
        $this->message = $message;
        $this->chat = $chat;
        $this->rawData = $rawData;
    }

    /**
     * Check if this is a message delta event
     */
    public function isMessageDelta(): bool
    {
        return $this->event === ChatEventType::CONVERSATION_MESSAGE_DELTA;
    }

    /**
     * Check if this is a chat completed event
     */
    public function isChatCompleted(): bool
    {
        return $this->event === ChatEventType::CONVERSATION_CHAT_COMPLETED;
    }

    /**
     * Check if this is a done event
     */
    public function isDone(): bool
    {
        return $this->event === ChatEventType::DONE;
    }

    /**
     * Check if this is an error event
     */
    public function isError(): bool
    {
        return $this->event === ChatEventType::ERROR;
    }
}
