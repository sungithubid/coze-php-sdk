<?php

/**
 * Coze PHP SDK - Message CRUD Example
 *
 * This example demonstrates how to create, list, retrieve, update and delete messages
 * within a conversation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;

// Get configuration from environment variables
$token = getenv('COZE_API_TOKEN') ?: 'your_access_token';
$botId = getenv('COZE_BOT_ID') ?: 'your_bot_id';
$baseUrl = getenv('COZE_API_BASE') ?: CozeClient::BASE_URL_CN;

// Initialize the client
$client = new CozeClient(
    new TokenAuth($token),
    $baseUrl
);

echo "=== Message CRUD Example ===\n\n";

try {
    // First, create a conversation to work with
    echo "0. Creating a conversation first...\n";
    $convResp = $client->conversations->create([
        'bot_id' => $botId,
    ]);

    if (!isset($convResp['data']['id'])) {
        echo "   Failed to create conversation\n";
        print_r($convResp);
        exit(1);
    }

    $conversationId = $convResp['data']['id'];
    echo "   Conversation ID: {$conversationId}\n\n";

    // 1. Create a message
    echo "1. Creating message...\n";
    $createResp = $client->conversations->messages->create([
        'conversation_id' => $conversationId,
        'role' => 'user',
        'content' => 'Hello, this is a test message from PHP SDK!',
        'content_type' => 'text',
        'meta_data' => [
            'source' => 'PHP SDK Example',
        ],
    ]);

    if (isset($createResp['data']['id'])) {
        $messageId = $createResp['data']['id'];
        echo "   Created message ID: {$messageId}\n";
        echo "   Content: " . ($createResp['data']['content'] ?? 'N/A') . "\n\n";
    } else {
        echo "   Failed to create message\n";
        print_r($createResp);
        exit(1);
    }

    // 2. List messages in the conversation
    echo "2. Listing messages...\n";
    $listResp = $client->conversations->messages->list([
        'conversation_id' => $conversationId,
        'limit' => 10,
    ]);

    if (isset($listResp['data'])) {
        $messages = $listResp['data'];
        echo "   Total messages: " . count($messages) . "\n";
        foreach ($messages as $msg) {
            echo "   - ID: " . ($msg['id'] ?? 'N/A');
            echo ", Role: " . ($msg['role'] ?? 'N/A');
            echo ", Content: " . substr($msg['content'] ?? '', 0, 50) . "...\n";
        }
    }
    echo "\n";

    // 3. Retrieve a specific message
    echo "3. Retrieving message...\n";
    $retrieveResp = $client->conversations->messages->retrieve($conversationId, $messageId);
    if (isset($retrieveResp['data'])) {
        $msg = $retrieveResp['data'];
        echo "   Message ID: " . ($msg['id'] ?? 'N/A') . "\n";
        echo "   Role: " . ($msg['role'] ?? 'N/A') . "\n";
        echo "   Content: " . ($msg['content'] ?? 'N/A') . "\n";
        echo "   Content Type: " . ($msg['content_type'] ?? 'N/A') . "\n";
        echo "   Created At: " . ($msg['created_at'] ?? 'N/A') . "\n";
    }
    echo "\n";

    // 4. Update the message
    echo "4. Updating message...\n";
    $updateResp = $client->conversations->messages->update([
        'conversation_id' => $conversationId,
        'message_id' => $messageId,
        'content' => 'Hello, this message has been updated!',
        'content_type' => 'text',
    ]);

    if (isset($updateResp['message'])) {
        echo "   Message updated successfully\n";
        echo "   New content: " . ($updateResp['message']['content'] ?? 'N/A') . "\n";
    }
    echo "\n";

    // 5. Delete the message
    echo "5. Deleting message...\n";
    $deleteResp = $client->conversations->messages->delete($conversationId, $messageId);
    if (isset($deleteResp['data'])) {
        echo "   Message deleted successfully\n";
        echo "   Deleted message ID: " . ($deleteResp['data']['id'] ?? 'N/A') . "\n";
    }
    echo "\n";

    echo "=== All operations completed successfully! ===\n";

} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
