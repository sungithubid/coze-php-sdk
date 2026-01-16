# Coze PHP SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://www.php.net/)

> ⚠️ **Note:** This is an unofficial third-party PHP SDK. Currently, only the commonly used **Chat** and **Datasets** APIs are implemented. Other APIs will be added gradually.

A PHP SDK for the Coze API. This SDK provides a convenient way to integrate Coze's AI capabilities into your PHP applications.

## Features

- 🚀 PHP SDK for the Coze API
- 🔐 Personal Access Token (PAT) authentication
- 📡 Both streaming and non-streaming chat support
- 🔄 Automatic polling for chat completion
- ⚡ Built on GuzzleHttp for reliable HTTP requests
- 📦 PSR-4 autoloading, ready for Packagist

## Requirements

- PHP 7.4 or higher
- Composer
- GuzzleHttp 7.0+

## Installation

```bash
composer require sungithubid/coze-php-sdk
```

Or add to your `composer.json`:

```json
{
    "require": {
        "sungithubid/coze-php-sdk": "^1.0"
    }
}
```

## Quick Start

### Initialize the Client

```php
use Coze\CozeClient;
use Coze\Auth\TokenAuth;

// Get your access token from https://www.coze.cn/open/oauth/pats
$token = 'your_access_token';

// Initialize the client (defaults to api.coze.cn)
$client = new CozeClient(
    new TokenAuth($token),
    CozeClient::BASE_URL_CN  // or CozeClient::BASE_URL_COM for global
);
```

### Streaming Chat

```php
use Coze\Models\Message;
use Coze\Chat\ChatEventType;

$stream = $client->chat->stream([
    'bot_id' => 'your_bot_id',
    'user_id' => 'user_123',
    'additional_messages' => [
        Message::buildUserQuestionText('Hello, how are you?'),
    ],
]);

foreach ($stream as $event) {
    if ($event->event === ChatEventType::CONVERSATION_MESSAGE_DELTA) {
        echo $event->message->content;
    }
    if ($event->event === ChatEventType::CONVERSATION_CHAT_COMPLETED) {
        echo "\nToken usage: " . $event->chat->usage->tokenCount . "\n";
    }
}
```

### Non-Streaming Chat

```php
use Coze\Models\Message;

$response = $client->chat->create([
    'bot_id' => 'your_bot_id',
    'user_id' => 'user_123',
    'additional_messages' => [
        Message::buildUserQuestionText('Hello!'),
    ],
]);

print_r($response);
```

### Chat with Auto-Polling

```php
use Coze\Models\Message;

// Create chat and automatically poll until completion
$chat = $client->chat->createAndPoll([
    'bot_id' => 'your_bot_id',
    'user_id' => 'user_123',
    'additional_messages' => [
        Message::buildUserQuestionText('What is the weather today?'),
    ],
]);

echo "Status: " . $chat->status . "\n";
echo "Token usage: " . $chat->usage->tokenCount . "\n";
```

## API Reference

### CozeClient

The main entry point for the SDK.

```php
$client = new CozeClient(
    AuthInterface $auth,      // Authentication handler
    string $baseUrl = 'https://api.coze.cn',  // API base URL
    array $options = []       // Additional HTTP client options
);
```

### Chat API

#### `$client->chat->create(array $request): array`

Create a non-streaming chat.

**Parameters:**
- `bot_id` (string, required): The ID of the bot
- `user_id` (string, required): The ID of the user
- `additional_messages` (array, required): List of messages
- `conversation_id` (string, optional): Conversation ID for context
- `auto_save_history` (bool, optional): Whether to save history (default: true)
- `meta_data` (array, optional): Additional metadata
- `parameters` (array, optional): Custom parameters

#### `$client->chat->stream(array $request): Generator`

Create a streaming chat that yields `ChatEvent` objects.

#### `$client->chat->createAndPoll(array $request, int $pollInterval = 1, int $timeout = 300): Chat`

Create a chat and poll until completion.

#### `$client->chat->retrieve(string $conversationId, string $chatId): array`

Retrieve chat status.

#### `$client->chat->cancel(string $conversationId, string $chatId): array`

Cancel a chat.

### Datasets API (Knowledge Base)

#### `$client->datasets->create(array $request): array`

Create a new dataset (knowledge base).

**Parameters:**
- `name` (string, required): Dataset name
- `space_id` (string, required): Space ID
- `format_type` (int, required): 0 = Document, 1 = Spreadsheet, 2 = Image
- `description` (string, optional): Dataset description

#### `$client->datasets->list(array $request): array`

List datasets in a space.

**Parameters:**
- `space_id` (string, required): Space ID
- `page_num` (int, optional): Page number (default: 1)
- `page_size` (int, optional): Page size (default: 10)

#### `$client->datasets->update(string $datasetId, array $request): array`

Update a dataset.

#### `$client->datasets->delete(string $datasetId): array`

Delete a dataset.

### Documents API (Knowledge Base Files)

#### `$client->datasets->documents->create(array $request): array`

Create documents in a dataset.

```php
use Coze\Models\DocumentBase;

$client->datasets->documents->create([
    'dataset_id' => 123456789,
    'document_bases' => [
        DocumentBase::buildLocalFile('doc.txt', 'File content here', 'txt'),
        DocumentBase::buildWebPage('Web Page', 'https://example.com', 24),
    ],
    'format_type' => 0,
]);
```

#### `$client->datasets->documents->list(array $request): array`

List documents in a dataset.

**Parameters:**
- `dataset_id` (int, required): Dataset ID
- `page` (int, optional): Page number (default: 1)
- `size` (int, optional): Page size (default: 20)

#### `$client->datasets->documents->update(array $request): array`

Update a document.

**Parameters:**
- `document_id` (int, required): Document ID
- `document_name` (string, optional): New document name

#### `$client->datasets->documents->delete(array $documentIds): array`

Delete documents.

### DocumentBase Helper Methods

```php
use Coze\Models\DocumentBase;

// Build from local file content
$doc = DocumentBase::buildLocalFile('name', 'content', 'txt');

// Build from web page URL
$doc = DocumentBase::buildWebPage('name', 'https://example.com', 24); // 24h update interval

// Build from image file ID
$doc = DocumentBase::buildImage('name', $fileId);
```

### Message Helper Methods

```php
use Coze\Models\Message;

// Build a user text question
$message = Message::buildUserQuestionText('Hello!');

// Build an assistant answer
$message = Message::buildAssistantAnswer('Hi there!');

// Build multimodal message with text and image
$message = Message::buildUserQuestionObjects([
    Message::textObject('What is in this image?'),
    Message::imageObjectByUrl('https://example.com/image.jpg'),
]);
```

### Event Types

```php
use Coze\Chat\ChatEventType;

ChatEventType::CONVERSATION_CHAT_CREATED;
ChatEventType::CONVERSATION_CHAT_IN_PROGRESS;
ChatEventType::CONVERSATION_CHAT_COMPLETED;
ChatEventType::CONVERSATION_CHAT_FAILED;
ChatEventType::CONVERSATION_CHAT_REQUIRES_ACTION;
ChatEventType::CONVERSATION_MESSAGE_DELTA;
ChatEventType::CONVERSATION_MESSAGE_COMPLETED;
ChatEventType::ERROR;
ChatEventType::DONE;
```

## Error Handling

```php
use Coze\Exceptions\ApiException;
use Coze\Exceptions\CozeException;

try {
    $response = $client->chat->create([...]);
} catch (ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . "\n";
    echo "Error Code: " . $e->getErrorCode() . "\n";
    echo "Log ID: " . $e->getLogId() . "\n";  // For debugging with Coze support
} catch (CozeException $e) {
    echo "SDK Error: " . $e->getMessage() . "\n";
}
```

## Examples

See the [examples](./examples) directory for more usage examples:

- [Streaming Chat](./examples/chat_stream.php)
- [Non-Streaming Chat](./examples/chat_no_stream.php)
- [Polling Chat](./examples/chat_poll.php)
- [Dataset CRUD](./examples/dataset_crud.php)
- [Document CRUD](./examples/document_crud.php)

## Environment Variables

You can configure the SDK using environment variables:

```bash
export COZE_API_TOKEN="your_access_token"
export COZE_API_BASE="https://api.coze.cn"  # or https://api.coze.com
export COZE_BOT_ID="your_bot_id"
export COZE_USER_ID="user_123"
```

## License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## TODO

- [ ] **Bots**
- [ ] **Conversations**
- [ ] **Workflows**
- [ ] **Workspaces**
- [ ] **Files**
- [ ] **Apps**
- [ ] **Folders**

## Related Links

- [Coze Official Website](https://www.coze.cn)
- [Coze API Documentation](https://www.coze.cn/open/docs/developer_guides)
- [Coze Python SDK](https://github.com/coze-dev/coze-py)
- [Coze Go SDK](https://github.com/coze-dev/coze-go)
