<?php

/**
 * Coze PHP SDK - Non-Streaming Chat Example
 *
 * This example demonstrates how to use the Coze SDK for non-streaming chat.
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

echo "Starting non-streaming chat...\n";
echo "Bot ID: {$botId}\n";
echo "User ID: {$userId}\n";
echo str_repeat('-', 50) . "\n";

try {
    // Create non-streaming chat request
    $response = $client->chat->create([
        'bot_id' => $botId,
        'user_id' => $userId,
        'additional_messages' => [
            Message::buildUserQuestionText('你好，请介绍一下你自己'),
        ],
        'auto_save_history' => true,
    ]);

    echo "Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    // Extract chat info
    if (isset($response['data'])) {
        $data = $response['data'];
        echo str_repeat('-', 50) . "\n";
        echo "Chat ID: " . ($data['id'] ?? 'N/A') . "\n";
        echo "Conversation ID: " . ($data['conversation_id'] ?? 'N/A') . "\n";
        echo "Status: " . ($data['status'] ?? 'N/A') . "\n";
    }
} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
