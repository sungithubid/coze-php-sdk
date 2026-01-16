<?php

/**
 * Coze PHP SDK - Polling Chat Example
 *
 * This example demonstrates how to use createAndPoll for automatic polling.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;
use Coze\Models\Message;

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

echo "Starting chat with auto-polling...\n";
echo "Bot ID: {$botId}\n";
echo "User ID: {$userId}\n";
echo str_repeat('-', 50) . "\n";

try {
    // Create and poll until completion
    $chat = $client->chat->createAndPoll([
        'bot_id' => $botId,
        'user_id' => $userId,
        'additional_messages' => [
            Message::buildUserQuestionText('你好'),
        ],
        'auto_save_history' => true,
    ]);

    echo "Chat completed!\n";
    echo str_repeat('-', 50) . "\n";
    echo "Chat ID: " . $chat->id . "\n";
    echo "Conversation ID: " . $chat->conversationId . "\n";
    echo "Status: " . $chat->status . "\n";

    if ($chat->usage) {
        echo "Token Usage: " . $chat->usage->tokenCount . "\n";
        echo "  - Input: " . $chat->usage->inputCount . "\n";
        echo "  - Output: " . $chat->usage->outputCount . "\n";
    }

    if ($chat->isFailed() && $chat->lastError) {
        echo "Error: " . $chat->lastError . "\n";
    }
} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
