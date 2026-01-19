<?php

declare(strict_types=1);

namespace Coze\Chat;

use Coze\Exceptions\CozeException;
use Coze\Http\HttpClient;
use Coze\Models\Chat;
use Generator;

/**
 * Chat API client
 */
class ChatClient
{
    private const CHAT_PATH = '/v3/chat';

    /** @var HttpClient */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create a chat (non-streaming)
     *
     * @param array $request Chat request parameters
     *   - bot_id: string (required) - The ID of the bot
     *   - user_id: string (required) - The ID of the user
     *   - additional_messages: array (required) - List of messages
     *   - stream: bool (optional) - Whether to stream (default: false)
     *   - conversation_id: string (optional) - Conversation ID for context
     *   - auto_save_history: bool (optional) - Whether to save history (default: true)
     *   - meta_data: array (optional) - Additional metadata
     *   - parameters: array (optional) - Custom parameters
     *
     * @return array Response data containing chat information
     * @throws CozeException
     */
    public function create(array $request): array
    {
        $request['stream'] = false;

        // Extract conversation_id for query parameter
        $path = self::CHAT_PATH;
        if (isset($request['conversation_id']) && $request['conversation_id'] !== '') {
            $path .= '?conversation_id=' . urlencode($request['conversation_id']);
            unset($request['conversation_id']);
        }

        $response = $this->httpClient->post($path, $request);

        return $response;
    }

    /**
     * Create a streaming chat
     *
     * @param array $request Chat request parameters (same as create)
     * @return Generator Generator yielding ChatEvent objects
     * @throws CozeException
     */
    public function stream(array $request): Generator
    {
        $request['stream'] = true;

        // Extract conversation_id for query parameter
        $path = self::CHAT_PATH;
        if (isset($request['conversation_id']) && $request['conversation_id'] !== '') {
            $path .= '?conversation_id=' . urlencode($request['conversation_id']);
            unset($request['conversation_id']);
        }

        $stream = $this->httpClient->postStream($path, $request);

        $iterator = new StreamIterator($stream);

        return $iterator->events();
    }

    /**
     * Create a chat and poll until completion
     *
     * @param array $request Chat request parameters
     * @param int $pollInterval Poll interval in seconds (default: 1)
     * @param int $timeout Timeout in seconds (default: 300)
     * @return Chat Completed chat object
     * @throws CozeException
     */
    public function createAndPoll(array $request, int $pollInterval = 1, int $timeout = 300): Chat
    {
        // First, create the chat without streaming
        $response = $this->create($request);

        if (!isset($response['data'])) {
            throw new CozeException('Invalid response: missing data field');
        }

        $chat = Chat::fromArray($response['data']);

        // If already completed, return immediately
        if ($chat->isCompleted() || $chat->isFailed()) {
            return $chat;
        }

        // Poll until completion
        $startTime = time();
        while ((time() - $startTime) < $timeout) {
            sleep($pollInterval);

            $statusResponse = $this->retrieve($chat->conversationId, $chat->id);

            if (!isset($statusResponse['data'])) {
                continue;
            }

            $chat = Chat::fromArray($statusResponse['data']);

            if ($chat->isCompleted() || $chat->isFailed() || $chat->requiresAction()) {
                return $chat;
            }
        }

        throw new CozeException('Chat polling timed out after ' . $timeout . ' seconds');
    }

    /**
     * Retrieve chat status
     *
     * @param string $conversationId Conversation ID
     * @param string $chatId Chat ID
     * @return array Response data
     * @throws CozeException
     */
    public function retrieve(string $conversationId, string $chatId): array
    {
        return $this->httpClient->get(self::CHAT_PATH . '/retrieve', [
            'conversation_id' => $conversationId,
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Submit tool outputs for a chat that requires action
     *
     * @param string $conversationId Conversation ID
     * @param string $chatId Chat ID
     * @param array $toolOutputs Tool outputs to submit
     * @param bool $stream Whether to stream the response
     * @return array|Generator Response data or stream generator
     * @throws CozeException
     */
    public function submitToolOutputs(
        string $conversationId,
        string $chatId,
        array $toolOutputs,
        bool $stream = false
    ) {
        $request = [
            'conversation_id' => $conversationId,
            'chat_id' => $chatId,
            'tool_outputs' => $toolOutputs,
            'stream' => $stream,
        ];

        if ($stream) {
            $streamResponse = $this->httpClient->postStream(
                self::CHAT_PATH . '/submit_tool_outputs',
                $request
            );
            $iterator = new StreamIterator($streamResponse);
            return $iterator->events();
        }

        return $this->httpClient->post(self::CHAT_PATH . '/submit_tool_outputs', $request);
    }

    /**
     * Cancel a chat
     *
     * @param string $conversationId Conversation ID
     * @param string $chatId Chat ID
     * @return array Response data
     * @throws CozeException
     */
    public function cancel(string $conversationId, string $chatId): array
    {
        return $this->httpClient->post(self::CHAT_PATH . '/cancel', [
            'conversation_id' => $conversationId,
            'chat_id' => $chatId,
        ]);
    }
}
