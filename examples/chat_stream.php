<?php

/**
 * Coze PHP SDK - Streaming Chat Example
 *
 * This example demonstrates how to use the Coze SDK for streaming chat.
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

echo "Starting streaming chat...\n";
echo "Bot ID: {$botId}\n";
echo "User ID: {$userId}\n";
echo str_repeat('-', 50) . "\n";

try {
    // Create streaming chat request
    $stream = $client->chat->stream([
        'bot_id' => $botId,
        'user_id' => $userId,
        'additional_messages' => [
            Message::buildUserQuestionText('介绍下江苏省造业贷款财政贴息实施细则政策呢'),
        ],
        'auto_save_history' => true,
    ]);

    // Process streaming events
    foreach ($stream as $event) {
        switch ($event->event) {
            case ChatEventType::CONVERSATION_CHAT_CREATED:
                $chatId = ($event->chat !== null) ? $event->chat->id : 'N/A';
                echo "[Chat Created] ID: " . $chatId . "\n";
                break;

            case ChatEventType::CONVERSATION_MESSAGE_DELTA:
                // Print message content incrementally
                $content = ($event->message !== null) ? $event->message->content : '';
                echo $content;
                break;

            case ChatEventType::CONVERSATION_MESSAGE_COMPLETED:
                echo "\n[Message Completed]\n";
                break;

            case ChatEventType::CONVERSATION_CHAT_COMPLETED:
                echo "\n" . str_repeat('-', 50) . "\n";
                echo "[Chat Completed]\n";
                if ($event->chat !== null && $event->chat->usage !== null) {
                    echo "Token Usage: " . $event->chat->usage->tokenCount . "\n";
                    echo "  - Input: " . $event->chat->usage->inputCount . "\n";
                    echo "  - Output: " . $event->chat->usage->outputCount . "\n";
                }
                break;

            case ChatEventType::CONVERSATION_CHAT_FAILED:
                echo "\n[Chat Failed]\n";
                if ($event->chat !== null && $event->chat->lastError !== null) {
                    echo "Error: " . $event->chat->lastError . "\n";
                }
                break;

            case ChatEventType::ERROR:
                echo "\n[Error] " . json_encode($event->rawData) . "\n";
                break;

            case ChatEventType::DONE:
                echo "[Stream Done]\n";
                break;
        }
    }
} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
