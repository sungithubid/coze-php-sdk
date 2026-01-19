<?php

/**
 * Coze PHP SDK - Chat with Conversation Example
 *
 * This example demonstrates how to use the Coze SDK to chat within an existing conversation.
 * By providing a conversation_id, you can continue a conversation with context preserved.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;
use Coze\Models\Message;
use Coze\Chat\ChatEventType;

// Get configuration from environment variables
$token = getenv('COZE_API_TOKEN') ?: 'your_access_token';
$botId = getenv('COZE_BOT_ID') ?: 'your_bot_id';
$userId = getenv('COZE_USER_ID') ?: '123';
$baseUrl = getenv('COZE_API_BASE') ?: CozeClient::BASE_URL_CN;

// Initialize the client
$client = new CozeClient(
    new TokenAuth($token),
    $baseUrl
);

echo "=== Chat with Conversation Example ===\n\n";

try {
    // Step 1: Create a new conversation first
    echo "Step 1: Creating a new conversation...\n";
    $convResp = $client->conversations->create([
        'bot_id' => $botId,
    ]);

    if (!isset($convResp['data']['id'])) {
        echo "Failed to create conversation\n";
        print_r($convResp);
        exit(1);
    }

    $conversationId = $convResp['data']['id'];
    echo "   Conversation ID: {$conversationId}\n\n";

    // Step 2: Send first message (non-streaming)
    echo "Step 2: Sending first message (non-streaming)...\n";
    $response1 = $client->chat->create([
        'bot_id' => $botId,
        'user_id' => $userId,
        'conversation_id' => $conversationId,  // Use the conversation ID
        'additional_messages' => [
            Message::buildUserQuestionText('你好，请记住我的名字是小明'),
        ],
        'auto_save_history' => true,
    ]);

    if (isset($response1['data'])) {
        echo "   Chat ID: " . ($response1['data']['id'] ?? 'N/A') . "\n";
        echo "   Status: " . ($response1['data']['status'] ?? 'N/A') . "\n";
    }
    echo "\n";

    // Wait a moment for the response to be processed
    sleep(2);

    // Step 3: Send follow-up message (streaming) - context should be preserved
    echo "Step 3: Sending follow-up message (streaming) - asking about what I told before...\n";
    echo str_repeat('-', 50) . "\n";

    $stream = $client->chat->stream([
        'bot_id' => $botId,
        'user_id' => $userId,
        'conversation_id' => $conversationId,  // Same conversation ID
        'additional_messages' => [
            Message::buildUserQuestionText('请问我的名字是什么？'),
        ],
        'auto_save_history' => true,
    ]);

    // Process streaming events
    foreach ($stream as $event) {
        switch ($event->event) {
            case ChatEventType::CONVERSATION_MESSAGE_DELTA:
                $content = ($event->message !== null) ? $event->message->content : '';
                echo $content;
                break;

            case ChatEventType::CONVERSATION_CHAT_COMPLETED:
                echo "\n" . str_repeat('-', 50) . "\n";
                echo "[Chat Completed]\n";
                if ($event->chat !== null && $event->chat->usage !== null) {
                    echo "Token Usage: " . $event->chat->usage->tokenCount . "\n";
                }
                break;

            case ChatEventType::CONVERSATION_CHAT_FAILED:
                echo "\n[Chat Failed]\n";
                if ($event->chat !== null && $event->chat->lastError !== null) {
                    echo "Error: " . $event->chat->lastError . "\n";
                }
                break;

            case ChatEventType::DONE:
                echo "[Stream Done]\n";
                break;
        }
    }

    echo "\n=== The bot should remember 'Xiaoming' from the conversation context! ===\n";

} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
