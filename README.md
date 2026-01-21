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
- 🎯 PHP Generator pattern (`yield $event`) for memory-efficient streaming

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

$client = new CozeClient(
    new TokenAuth('your_access_token'),
    CozeClient::BASE_URL_CN  // or CozeClient::BASE_URL_COM for global
);
```

### Chat Examples

| Example | Method | Description |
|---------|--------|-------------|
| [chat_stream.php](./examples/chat_stream.php) | `$client->chat->stream()` | Streaming chat using PHP Generator (`yield`), memory-efficient for large responses |
| [chat_no_stream.php](./examples/chat_no_stream.php) | `$client->chat->create()` | Non-streaming chat, returns complete response |
| [chat_poll.php](./examples/chat_poll.php) | `$client->chat->createAndPoll()` | Auto-polling until chat completion |
| [chat_with_conversation.php](./examples/chat_with_conversation.php) | `$client->chat->create()` | Chat with conversation context |

### Conversation & Message Examples

| Example | Description |
|---------|-------------|
| [conversation_crud.php](./examples/conversation_crud.php) | Create, retrieve, list, and clear conversations |
| [message_crud.php](./examples/message_crud.php) | Create, retrieve, list, update, and delete messages |

### Dataset Examples

| Example | Description |
|---------|-------------|
| [dataset_crud.php](./examples/dataset_crud.php) | Create, list, update, and delete datasets (knowledge bases) |
| [document_crud.php](./examples/document_crud.php) | Create, list, update, and delete documents in datasets |

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

### Conversations API

#### `$client->conversations->create(array $request): array`

Create a new conversation.

**Parameters:**
- `messages` (array, optional): Initial messages in the conversation
- `meta_data` (array, optional): Additional metadata
- `bot_id` (string, optional): Bot ID to bind the conversation
- `connector_id` (string, optional): Connector ID (999: Chat SDK, 1024: API)

```php
$response = $client->conversations->create([
    'bot_id' => 'your_bot_id',
    'meta_data' => [
        'user_name' => 'Test User',
    ],
]);
$conversationId = $response['data']['id'];
```

#### `$client->conversations->retrieve(string $conversationId): array`

Retrieve conversation details.

#### `$client->conversations->list(array $request): array`

List conversations for a bot.

**Parameters:**
- `bot_id` (string, required): Bot ID
- `page_num` (int, optional): Page number (default: 1)
- `page_size` (int, optional): Page size (default: 20)

#### `$client->conversations->clear(string $conversationId): array`

Clear conversation context/history.

### Messages API

#### `$client->conversations->messages->create(array $request): array`

Create a message in a conversation.

**Parameters:**
- `conversation_id` (string, required): Conversation ID
- `role` (string, required): Message role ('user' or 'assistant')
- `content` (string, required): Message content
- `content_type` (string, required): Content type ('text' or 'object_string')
- `meta_data` (array, optional): Additional metadata

```php
$response = $client->conversations->messages->create([
    'conversation_id' => $conversationId,
    'role' => 'user',
    'content' => 'Hello, how are you?',
    'content_type' => 'text',
]);
```

#### `$client->conversations->messages->list(array $request): array`

List messages in a conversation.

**Parameters:**
- `conversation_id` (string, required): Conversation ID
- `order` (string, optional): Sorting order ('asc' or 'desc')
- `chat_id` (string, optional): Filter by chat ID
- `before_id` (string, optional): Get messages before this ID
- `after_id` (string, optional): Get messages after this ID
- `limit` (int, optional): Number of messages (default: 50, max: 50)

#### `$client->conversations->messages->retrieve(string $conversationId, string $messageId): array`

Retrieve a specific message.

#### `$client->conversations->messages->update(array $request): array`

Update a message.

**Parameters:**
- `conversation_id` (string, required): Conversation ID
- `message_id` (string, required): Message ID
- `content` (string, optional): New message content
- `content_type` (string, optional): New content type
- `meta_data` (array, optional): New metadata

#### `$client->conversations->messages->delete(string $conversationId, string $messageId): array`

Delete a message.

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

- [x] **Conversations**
- [ ] **Bots**
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
