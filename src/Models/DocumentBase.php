<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Document base for creating documents
 */
class DocumentBase
{
    /** @var string */
    public $name;

    /** @var array */
    public $sourceInfo;

    /** @var array|null */
    public $updateRule;

    public function __construct(string $name, array $sourceInfo, ?array $updateRule = null)
    {
        $this->name = $name;
        $this->sourceInfo = $sourceInfo;
        $this->updateRule = $updateRule;
    }

    /**
     * Build document base for local file upload
     *
     * @param string $name Document name
     * @param string $content Raw file content (binary, NOT base64 encoded)
     * @param string $fileType File extension (txt, pdf, doc, docx)
     */
    public static function buildLocalFile(string $name, string $content, string $fileType): self
    {
        return new self($name, [
            'file_base64' => base64_encode($content),
            'file_type' => $fileType,
            'document_source' => 0,  // 0: Upload local files
        ]);
    }

    /**
     * Build document base for web page
     *
     * @param string $name Document name
     * @param string $url Web page URL
     * @param int|null $updateInterval Auto update interval in hours (min 24)
     */
    public static function buildWebPage(string $name, string $url, ?int $updateInterval = null): self
    {
        $updateRule = ['update_type' => DocumentUpdateType::NO_AUTO_UPDATE];
        if ($updateInterval !== null) {
            $updateRule = [
                'update_type' => DocumentUpdateType::AUTO_UPDATE,
                'update_interval' => $updateInterval,
            ];
        }

        return new self(
            $name,
            [
                'web_url' => $url,
                'document_source' => 1,
            ],
            $updateRule
        );
    }

    /**
     * Build document base for image (using file_id)
     *
     * @param string $name Document name
     * @param int $fileId Uploaded file ID
     */
    public static function buildImage(string $name, int $fileId): self
    {
        return new self($name, [
            'source_file_id' => $fileId,
            'document_source' => 5,
        ]);
    }

    /**
     * Convert to array for API request
     */
    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'source_info' => $this->sourceInfo,
        ];

        if ($this->updateRule !== null) {
            $result['update_rule'] = $this->updateRule;
        }

        return $result;
    }
}
