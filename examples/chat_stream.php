<?php

/**
 * Coze PHP SDK - Streaming Chat Example
 *
 * This example demonstrates how to use the Coze SDK for streaming chat,
 * including handling different message types:
 * - answer: Regular response content
 * - function_call: Tool/function calls made by the assistant
 * - tool_response: Responses from tool/function calls
 * - follow_up: Suggested follow-up questions
 * - verbose: Debug/internal messages (e.g., knowledge recall)
 * - reasoning_content: Deep thinking content (for reasoning models)
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
            Message::buildUserQuestionText('介绍下医沛生公司'),
        ],
        'auto_save_history' => true,
    ]);

    // Collect follow-up suggestions
    $followUpSuggestions = [];

    // Process streaming events
    foreach ($stream as $event) {
        switch ($event->event) {
            case ChatEventType::CONVERSATION_CHAT_CREATED:
                $chatId = ($event->chat !== null) ? $event->chat->id : 'N/A';
                echo "[Chat Created] ID: " . $chatId . "\n";
                break;

            case ChatEventType::CONVERSATION_CHAT_IN_PROGRESS:
                echo "[Chat In Progress]\n";
                break;

            case ChatEventType::CONVERSATION_MESSAGE_DELTA:
                // Handle streaming message delta
                if ($event->message !== null) {
                    // Check for reasoning content (deep thinking)
                    if ($event->message->hasReasoningContent()) {
                        // Reasoning content - typically shown in a separate area or collapsed
                        echo "\033[90m" . $event->message->reasoningContent . "\033[0m"; // Gray color for reasoning
                    }

                    // Regular content output
                    if (!empty($event->message->content)) {
                        echo $event->message->content;
                    }
                }
                break;

            case ChatEventType::CONVERSATION_MESSAGE_COMPLETED:
                // Handle completed messages based on type
                if ($event->message !== null) {
                    $message = $event->message;

                    switch ($message->type) {
                        case Message::TYPE_ANSWER:
                            // Final answer message completed
                            echo "\n[Answer Completed]\n";
                            break;

                        case Message::TYPE_FUNCTION_CALL:
                            // Function/tool call - parse the content for details
                            echo "\n[Function Call]\n";
                            $callData = json_decode($message->content, true);
                            if ($callData) {
                                echo "  Plugin: " . ($callData['plugin'] ?? 'N/A') . "\n";
                                echo "  API: " . ($callData['api_name'] ?? 'N/A') . "\n";
                                echo "  Arguments: " . json_encode($callData['arguments'] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
                            }
                            break;

                        case Message::TYPE_TOOL_RESPONSE:
                            // Tool response - show the result
                            echo "[Tool Response]\n";
                            // Optionally parse and display the response
                            $responseData = json_decode($message->content, true);
                            if ($responseData && is_array($responseData)) {
                                echo "  Results: " . count($responseData) . " item(s)\n";
                            }
                            break;

                        case Message::TYPE_FOLLOW_UP:
                            // Collect follow-up suggestions to show at the end
                            $followUpSuggestions[] = $message->content;
                            break;

                        case Message::TYPE_VERBOSE:
                            // Verbose/debug messages - usually hidden or logged
                            // Uncomment to see verbose messages:
                            // echo "[Verbose] " . substr($message->content, 0, 100) . "...\n";
                            break;
                    }
                }
                break;

            case ChatEventType::CONVERSATION_CHAT_COMPLETED:
                echo "\n" . str_repeat('-', 50) . "\n";
                echo "[Chat Completed]\n";
                if ($event->chat !== null && $event->chat->usage !== null) {
                    echo "Token Usage: " . $event->chat->usage->tokenCount . "\n";
                    echo "  - Input: " . $event->chat->usage->inputCount . "\n";
                    echo "  - Output: " . $event->chat->usage->outputCount . "\n";
                }

                // Show follow-up suggestions
                if (!empty($followUpSuggestions)) {
                    echo "\nSuggested Follow-ups:\n";
                    foreach ($followUpSuggestions as $index => $suggestion) {
                        echo "  " . ($index + 1) . ". " . $suggestion . "\n";
                    }
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
