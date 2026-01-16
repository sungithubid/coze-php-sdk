<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Token usage statistics
 */
class Usage
{
    /** @var int */
    public $tokenCount;

    /** @var int */
    public $outputCount;

    /** @var int */
    public $inputCount;

    public function __construct(int $tokenCount, int $outputCount, int $inputCount)
    {
        $this->tokenCount = $tokenCount;
        $this->outputCount = $outputCount;
        $this->inputCount = $inputCount;
    }

    /**
     * Create a Usage instance from API response data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['token_count'] ?? 0,
            $data['output_count'] ?? 0,
            $data['input_count'] ?? 0
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'token_count' => $this->tokenCount,
            'output_count' => $this->outputCount,
            'input_count' => $this->inputCount,
        ];
    }
}
