<?php

declare(strict_types=1);

namespace Coze\Conversations;

use Coze\Exceptions\CozeException;
use Coze\Http\HttpClient;

/**
 * Conversations API client for managing conversations
 */
class ConversationsClient
{
    /** @var HttpClient */
    private $httpClient;

    /** @var MessagesClient */
    public $messages;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->messages = new MessagesClient($httpClient);
    }

    /**
     * Create a new conversation
     *
     * @param array $request Create request
     *   - messages: array (optional) - Initial messages in the conversation
     *   - meta_data: array (optional) - Additional metadata
     *   - bot_id: string (optional) - Bot ID to bind the conversation
     *   - connector_id: string (optional) - Connector ID (999: Chat SDK, 1024: API)
     *
     * @return array Response with conversation data
     * @throws CozeException
     */
    public function create(array $request = []): array
    {
        return $this->httpClient->post('/v1/conversation/create', $request);
    }

    /**
     * Retrieve conversation details
     *
     * @param string $conversationId Conversation ID
     * @return array Response with conversation data
     * @throws CozeException
     */
    public function retrieve(string $conversationId): array
    {
        return $this->httpClient->get('/v1/conversation/retrieve', [
            'conversation_id' => $conversationId,
        ]);
    }

    /**
     * List conversations for a bot
     *
     * @param array $request List request
     *   - bot_id: string (required) - Bot ID
     *   - page_num: int (optional) - Page number (default: 1)
     *   - page_size: int (optional) - Page size (default: 20)
     *
     * @return array Response with conversations list
     * @throws CozeException
     */
    public function list(array $request): array
    {
        $query = [
            'bot_id' => $request['bot_id'],
        ];

        if (isset($request['page_num'])) {
            $query['page_num'] = $request['page_num'];
        }
        if (isset($request['page_size'])) {
            $query['page_size'] = $request['page_size'];
        }

        return $this->httpClient->get('/v1/conversations', $query);
    }

    /**
     * Clear conversation context
     *
     * Clears the context/history of a conversation, creating a new section.
     *
     * @param string $conversationId Conversation ID
     * @return array Response with clear result
     * @throws CozeException
     */
    public function clear(string $conversationId): array
    {
        return $this->httpClient->post(
            '/v1/conversations/' . urlencode($conversationId) . '/clear',
            []
        );
    }
}
