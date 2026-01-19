<?php

declare(strict_types=1);

namespace Coze\Conversations;

use Coze\Exceptions\CozeException;
use Coze\Http\HttpClient;

/**
 * Messages API client for managing messages in conversations
 */
class MessagesClient
{
    /** @var HttpClient */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create a message in a conversation
     *
     * @param array $request Create request
     *   - conversation_id: string (required) - Conversation ID
     *   - role: string (required) - Message role ('user' or 'assistant')
     *   - content: string (required) - Message content
     *   - content_type: string (required) - Content type ('text' or 'object_string')
     *   - meta_data: array (optional) - Additional metadata
     *
     * @return array Response with message data
     * @throws CozeException
     */
    public function create(array $request): array
    {
        $conversationId = $request['conversation_id'] ?? '';
        unset($request['conversation_id']);

        return $this->httpClient->post(
            '/v1/conversation/message/create?conversation_id=' . urlencode($conversationId),
            $request
        );
    }

    /**
     * List messages in a conversation
     *
     * @param array $request List request
     *   - conversation_id: string (required) - Conversation ID
     *   - order: string (optional) - Sorting order ('asc' or 'desc')
     *   - chat_id: string (optional) - Filter by chat ID
     *   - before_id: string (optional) - Get messages before this ID
     *   - after_id: string (optional) - Get messages after this ID
     *   - limit: int (optional) - Number of messages to return (default: 50, max: 50)
     *
     * @return array Response with messages list
     * @throws CozeException
     */
    public function list(array $request): array
    {
        $conversationId = $request['conversation_id'] ?? '';
        unset($request['conversation_id']);

        return $this->httpClient->post(
            '/v1/conversation/message/list?conversation_id=' . urlencode($conversationId),
            $request
        );
    }

    /**
     * Retrieve a specific message
     *
     * @param string $conversationId Conversation ID
     * @param string $messageId Message ID
     * @return array Response with message data
     * @throws CozeException
     */
    public function retrieve(string $conversationId, string $messageId): array
    {
        return $this->httpClient->get('/v1/conversation/message/retrieve', [
            'conversation_id' => $conversationId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Update a message
     *
     * @param array $request Update request
     *   - conversation_id: string (required) - Conversation ID
     *   - message_id: string (required) - Message ID
     *   - content: string (optional) - New message content
     *   - content_type: string (optional) - New content type
     *   - meta_data: array (optional) - New metadata
     *
     * @return array Response with updated message data
     * @throws CozeException
     */
    public function update(array $request): array
    {
        $conversationId = $request['conversation_id'] ?? '';
        $messageId = $request['message_id'] ?? '';
        unset($request['conversation_id'], $request['message_id']);

        return $this->httpClient->post(
            '/v1/conversation/message/modify?conversation_id=' . urlencode($conversationId) .
            '&message_id=' . urlencode($messageId),
            $request
        );
    }

    /**
     * Delete a message
     *
     * @param string $conversationId Conversation ID
     * @param string $messageId Message ID
     * @return array Response with deleted message data
     * @throws CozeException
     */
    public function delete(string $conversationId, string $messageId): array
    {
        return $this->httpClient->post(
            '/v1/conversation/message/delete?conversation_id=' . urlencode($conversationId) .
            '&message_id=' . urlencode($messageId),
            []
        );
    }
}
