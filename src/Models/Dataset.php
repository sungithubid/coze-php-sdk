<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Dataset (Knowledge Base) model
 */
class Dataset
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var string */
    public $spaceId;

    /** @var int */
    public $status;

    /** @var int */
    public $formatType;

    /** @var bool */
    public $canEdit;

    /** @var string|null */
    public $iconUrl;

    /** @var int */
    public $docCount;

    /** @var int */
    public $hitCount;

    /** @var int */
    public $sliceCount;

    /** @var string|null */
    public $allFileSize;

    /** @var int|null */
    public $createTime;

    /** @var int|null */
    public $updateTime;

    public function __construct(
        string  $id,
        string  $name,
        string  $description = '',
        string  $spaceId = '',
        int     $status = 1,
        int     $formatType = 0,
        bool    $canEdit = true,
        ?string $iconUrl = null,
        int     $docCount = 0,
        int     $hitCount = 0,
        int     $sliceCount = 0,
        ?string $allFileSize = null,
        ?int    $createTime = null,
        ?int    $updateTime = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->spaceId = $spaceId;
        $this->status = $status;
        $this->formatType = $formatType;
        $this->canEdit = $canEdit;
        $this->iconUrl = $iconUrl;
        $this->docCount = $docCount;
        $this->hitCount = $hitCount;
        $this->sliceCount = $sliceCount;
        $this->allFileSize = $allFileSize;
        $this->createTime = $createTime;
        $this->updateTime = $updateTime;
    }

    /**
     * Create from API response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['dataset_id'] ?? '',
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['space_id'] ?? '',
            $data['status'] ?? 1,
            $data['format_type'] ?? 0,
            $data['can_edit'] ?? true,
            $data['icon_url'] ?? null,
            $data['doc_count'] ?? 0,
            $data['hit_count'] ?? 0,
            $data['slice_count'] ?? 0,
            $data['all_file_size'] ?? null,
            $data['create_time'] ?? null,
            $data['update_time'] ?? null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'dataset_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'space_id' => $this->spaceId,
            'status' => $this->status,
            'format_type' => $this->formatType,
            'can_edit' => $this->canEdit,
            'icon_url' => $this->iconUrl,
            'doc_count' => $this->docCount,
            'hit_count' => $this->hitCount,
            'slice_count' => $this->sliceCount,
            'all_file_size' => $this->allFileSize,
            'create_time' => $this->createTime,
            'update_time' => $this->updateTime,
        ], function ($v) {
            return $v !== null;
        });
    }
}

/**
 * Dataset status constants
 */
class DatasetStatus
{
    public const ENABLED = 1;
    public const DISABLED = 3;
}