<?php

declare(strict_types=1);

namespace Coze\Chat;

use Coze\Models\Chat;
use Coze\Models\Message;
use Generator;
use Psr\Http\Message\StreamInterface;

/**
 * Stream iterator for SSE responses
 */
class StreamIterator
{
    /** @var StreamInterface */
    private $stream;

    /** @var string */
    private $buffer = '';

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Iterate over SSE events
     *
     * @return Generator
     */
    public function events(): Generator
    {
        while (!$this->stream->eof()) {
            $chunk = $this->stream->read(8192);
            if ($chunk === '') {
                continue;
            }

            $this->buffer .= $chunk;

            // Process complete events from buffer
            while (($eventEnd = strpos($this->buffer, "\n\n")) !== false) {
                $eventData = substr($this->buffer, 0, $eventEnd);
                $this->buffer = substr($this->buffer, $eventEnd + 2);

                $event = $this->parseEvent($eventData);
                if ($event !== null) {
                    yield $event;

                    // Stop if we receive done event
                    if ($event->isDone()) {
                        return;
                    }
                }
            }
        }

        // Process any remaining data in buffer
        if (!empty($this->buffer)) {
            $event = $this->parseEvent($this->buffer);
            if ($event !== null) {
                yield $event;
            }
        }
    }

    /**
     * Parse SSE event data
     */
    private function parseEvent(string $eventData): ?ChatEvent
    {
        $lines = explode("\n", $eventData);
        $event = '';
        $data = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'event:') === 0) {
                $event = trim(substr($line, 6));
            } elseif (strpos($line, 'data:') === 0) {
                $data = trim(substr($line, 5));
            }
        }

        if (empty($event) && empty($data)) {
            return null;
        }

        // Handle [DONE] marker or done event
        if ($data === '[DONE]' || $event === 'done' || $event === ChatEventType::DONE) {
            return new ChatEvent(ChatEventType::DONE);
        }

        // Parse JSON data
        $jsonData = null;
        if (!empty($data)) {
            $decoded = json_decode($data, true);
            // Only use jsonData if it's a valid array
            if (is_array($decoded)) {
                $jsonData = $decoded;
            }
        }

        // Create event based on type
        switch ($event) {
            case ChatEventType::CONVERSATION_MESSAGE_DELTA:
            case ChatEventType::CONVERSATION_MESSAGE_COMPLETED:
                return new ChatEvent(
                    $event,
                    $jsonData ? Message::fromArray($jsonData) : null,
                    null,
                    $jsonData
                );

            case ChatEventType::CONVERSATION_CHAT_CREATED:
            case ChatEventType::CONVERSATION_CHAT_IN_PROGRESS:
            case ChatEventType::CONVERSATION_CHAT_COMPLETED:
            case ChatEventType::CONVERSATION_CHAT_FAILED:
            case ChatEventType::CONVERSATION_CHAT_REQUIRES_ACTION:
                return new ChatEvent(
                    $event,
                    null,
                    $jsonData ? Chat::fromArray($jsonData) : null,
                    $jsonData
                );

            case ChatEventType::ERROR:
                return new ChatEvent(
                    $event,
                    null,
                    null,
                    $jsonData
                );

            default:
                return new ChatEvent(
                    $event ?: 'unknown',
                    null,
                    null,
                    $jsonData
                );
        }
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        $this->stream->close();
    }
}
