<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Document model for knowledge base files
 */
class Document
{
    /** @var string */
    public $documentId;

    /** @var string */
    public $name;

    /** @var int */
    public $charCount;

    /** @var int */
    public $sliceCount;

    /** @var int */
    public $size;

    /** @var int */
    public $formatType;

    /** @var int */
    public $sourceType;

    /** @var int */
    public $status;

    /** @var string|null */
    public $type;

    /** @var int */
    public $hitCount;

    /** @var int */
    public $updateInterval;

    /** @var int */
    public $updateType;

    /** @var int|null */
    public $createTime;

    /** @var int|null */
    public $updateTime;

    public function __construct(
        string $documentId,
        string $name,
        int $charCount = 0,
        int $sliceCount = 0,
        int $size = 0,
        int $formatType = 0,
        int $sourceType = 0,
        int $status = 0,
        ?string $type = null,
        int $hitCount = 0,
        int $updateInterval = 0,
        int $updateType = 0,
        ?int $createTime = null,
        ?int $updateTime = null
    ) {
        $this->documentId = $documentId;
        $this->name = $name;
        $this->charCount = $charCount;
        $this->sliceCount = $sliceCount;
        $this->size = $size;
        $this->formatType = $formatType;
        $this->sourceType = $sourceType;
        $this->status = $status;
        $this->type = $type;
        $this->hitCount = $hitCount;
        $this->updateInterval = $updateInterval;
        $this->updateType = $updateType;
        $this->createTime = $createTime;
        $this->updateTime = $updateTime;
    }

    /**
     * Create from API response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['document_id'] ?? '',
            $data['name'] ?? '',
            $data['char_count'] ?? 0,
            $data['slice_count'] ?? 0,
            $data['size'] ?? 0,
            $data['format_type'] ?? 0,
            $data['source_type'] ?? 0,
            $data['status'] ?? 0,
            $data['type'] ?? null,
            $data['hit_count'] ?? 0,
            $data['update_interval'] ?? 0,
            $data['update_type'] ?? 0,
            $data['create_time'] ?? null,
            $data['update_time'] ?? null
        );
    }

    /**
     * Check if document is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === DocumentStatus::PROCESSING;
    }

    /**
     * Check if document is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === DocumentStatus::COMPLETED;
    }

    /**
     * Check if document processing failed
     */
    public function isFailed(): bool
    {
        return $this->status === DocumentStatus::FAILED;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'document_id' => $this->documentId,
            'name' => $this->name,
            'char_count' => $this->charCount,
            'slice_count' => $this->sliceCount,
            'size' => $this->size,
            'format_type' => $this->formatType,
            'source_type' => $this->sourceType,
            'status' => $this->status,
            'type' => $this->type,
            'hit_count' => $this->hitCount,
            'update_interval' => $this->updateInterval,
            'update_type' => $this->updateType,
            'create_time' => $this->createTime,
            'update_time' => $this->updateTime,
        ], function ($v) {
            return $v !== null; });
    }
}

/**
 * Document status constants
 */
class DocumentStatus
{
    /** Processing */
    public const PROCESSING = 0;
    /** Completed */
    public const COMPLETED = 1;
    /** Failed */
    public const FAILED = 9;
}

/**
 * Document source type constants
 */
class DocumentSourceType
{
    /** Local file upload */
    public const LOCAL_FILE = 0;
    /** Online web page */
    public const WEB_PAGE = 1;
}

/**
 * Document update type constants
 */
class DocumentUpdateType
{
    /** No auto update */
    public const NO_AUTO_UPDATE = 0;
    /** Auto update */
    public const AUTO_UPDATE = 1;
}
