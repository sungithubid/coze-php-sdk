<?php

/**
 * Coze PHP SDK - Conversation CRUD Example
 *
 * This example demonstrates how to create, retrieve, list and clear conversations.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;
use Coze\Models\Message;

// Get configuration from environment variables
$token = getenv('COZE_API_TOKEN') ?: 'your_access_token';
$botId = getenv('COZE_BOT_ID') ?: 'your_bot_id';
$baseUrl = getenv('COZE_API_BASE') ?: CozeClient::BASE_URL_CN;

// Initialize the client
$client = new CozeClient(
    new TokenAuth($token),
    $baseUrl
);

echo "=== Conversation CRUD Example ===\n\n";

try {
    // 1. Create a new conversation
    echo "1. Creating conversation...\n";
    $createResp = $client->conversations->create([
        'bot_id' => $botId,
        'meta_data' => [
            'user_name' => 'Test User',
            'source' => 'PHP SDK Example',
        ],
    ]);

    if (isset($createResp['data']['id'])) {
        $conversationId = $createResp['data']['id'];
        echo "   Created conversation ID: {$conversationId}\n\n";
    } else {
        echo "   Failed to create conversation\n";
        print_r($createResp);
        exit(1);
    }

    // 2. Retrieve conversation details
    echo "2. Retrieving conversation...\n";
    $retrieveResp = $client->conversations->retrieve($conversationId);
    if (isset($retrieveResp['data'])) {
        $conv = $retrieveResp['data'];
        echo "   Conversation ID: " . ($conv['id'] ?? 'N/A') . "\n";
        echo "   Created At: " . ($conv['created_at'] ?? 'N/A') . "\n";
        echo "   Last Section ID: " . ($conv['last_section_id'] ?? 'N/A') . "\n";
        if (!empty($conv['meta_data'])) {
            echo "   Meta Data: " . json_encode($conv['meta_data']) . "\n";
        }
    }
    echo "\n";

    // 3. List conversations for the bot
    echo "3. Listing conversations...\n";
    $listResp = $client->conversations->list([
        'bot_id' => $botId,
        'page_num' => 1,
        'page_size' => 10,
    ]);

    if (isset($listResp['data']['conversations'])) {
        $conversations = $listResp['data']['conversations'];
        echo "   Total conversations: " . count($conversations) . "\n";
        foreach ($conversations as $conv) {
            echo "   - ID: " . ($conv['id'] ?? 'N/A');
            echo ", Created: " . ($conv['created_at'] ?? 'N/A') . "\n";
        }
    }
    echo "\n";

    // 4. Clear conversation context
    echo "4. Clearing conversation context...\n";
    $clearResp = $client->conversations->clear($conversationId);
    if (isset($clearResp['data'])) {
        echo "   Context cleared successfully\n";
        echo "   New Section ID: " . ($clearResp['data']['section_id'] ?? 'N/A') . "\n";
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
