<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Conversation model
 */
class Conversation
{
    /** @var string */
    public $id;

    /** @var int|null */
    public $createdAt;

    /** @var array|null */
    public $metaData;

    /** @var string|null */
    public $lastSectionId;

    public function __construct(
        string $id,
        ?int $createdAt = null,
        ?array $metaData = null,
        ?string $lastSectionId = null
    ) {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->metaData = $metaData;
        $this->lastSectionId = $lastSectionId;
    }

    /**
     * Create a Conversation instance from API response data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            isset($data['created_at']) ? (int) $data['created_at'] : null,
            $data['meta_data'] ?? null,
            $data['last_section_id'] ?? null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'meta_data' => $this->metaData,
            'last_section_id' => $this->lastSectionId,
        ], function ($v) {
            return $v !== null;
        });
    }
}
