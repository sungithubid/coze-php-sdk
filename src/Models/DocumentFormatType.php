<?php

declare(strict_types=1);

namespace Coze\Models;

/**
 * Document format type constants
 */
class DocumentFormatType
{
    /** Regular document (txt, pdf, doc, docx, etc.) */
    public const DOCUMENT = 0;
    /** Table format (xlsx, csv) */
    public const TABLE = 1;
    /** Image format */
    public const IMAGE = 2;
}
