<?php

/**
 * Coze PHP SDK - Streaming Chat Debug Example
 *
 * This example demonstrates how to debug streaming chat responses by printing each raw line from the API.
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

echo "=== Coze API Stream Response Debug ===\n";
echo "Bot ID: {$botId}\n";
echo "User ID: {$userId}\n";
echo str_repeat('=', 60) . "\n\n";

// Build request manually to use raw HTTP stream
$httpClient = new \GuzzleHttp\Client([
    'base_uri' => $baseUrl,
    'timeout' => 300,
    'stream' => true,
]);

$requestBody = [
    'bot_id' => $botId,
    'user_id' => $userId,
    'stream' => true,
    'auto_save_history' => true,
    'additional_messages' => [
        [
            'role' => 'user',
            'content' => '介绍下医沛生公司的产品',
            'content_type' => 'text',
        ],
    ],
];

try {
    $response = $httpClient->post('/v3/chat', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'text/event-stream',
        ],
        'json' => $requestBody,
    ]);

    $stream = $response->getBody();

    echo ">>> Raw SSE Response (line by line):\n";
    echo str_repeat('-', 60) . "\n";

    $lineNumber = 0;
    $buffer = '';

    while (!$stream->eof()) {
        $chunk = $stream->read(1024);
        if ($chunk === '') {
            continue;
        }

        $buffer .= $chunk;

        // Split by newlines to process each line
        while (($newlinePos = strpos($buffer, "\n")) !== false) {
            $line = substr($buffer, 0, $newlinePos);
            $buffer = substr($buffer, $newlinePos + 1);

            $lineNumber++;

            // Print the line with line number
            $displayLine = $line;
            if ($line === '') {
                $displayLine = '(empty line - event separator)';
            }

            echo sprintf("[Line %3d] %s\n", $lineNumber, $displayLine);
        }
    }

    // Handle remaining buffer
    if (!empty($buffer)) {
        $lineNumber++;
        echo sprintf("[Line %3d] %s\n", $lineNumber, $buffer);
    }

    echo str_repeat('-', 60) . "\n";
    echo ">>> Stream ended. Total lines: {$lineNumber}\n";

} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "HTTP Client Error: " . $e->getMessage() . "\n";
    echo "Response Body: " . $e->getResponse()->getBody()->getContents() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
