<?php

declare(strict_types=1);

namespace Coze\Chat;

/**
 * Chat event types for SSE streaming
 */
class ChatEventType
{
    /**
     * Conversation chat created event
     */
    public const CONVERSATION_CHAT_CREATED = 'conversation.chat.created';

    /**
     * Conversation chat in progress event
     */
    public const CONVERSATION_CHAT_IN_PROGRESS = 'conversation.chat.in_progress';

    /**
     * Conversation chat completed event
     */
    public const CONVERSATION_CHAT_COMPLETED = 'conversation.chat.completed';

    /**
     * Conversation chat failed event
     */
    public const CONVERSATION_CHAT_FAILED = 'conversation.chat.failed';

    /**
     * Conversation chat requires action event
     */
    public const CONVERSATION_CHAT_REQUIRES_ACTION = 'conversation.chat.requires_action';

    /**
     * Conversation message delta event (streaming content)
     */
    public const CONVERSATION_MESSAGE_DELTA = 'conversation.message.delta';

    /**
     * Conversation message completed event
     */
    public const CONVERSATION_MESSAGE_COMPLETED = 'conversation.message.completed';

    /**
     * Conversation audio delta event
     */
    public const CONVERSATION_AUDIO_DELTA = 'conversation.audio.delta';

    /**
     * Error event
     */
    public const ERROR = 'error';

    /**
     * Done event (stream finished)
     */
    public const DONE = 'done';
}
