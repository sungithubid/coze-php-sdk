<?php

/**
 * Coze PHP SDK - Document CRUD Example
 *
 * This example demonstrates how to create, list, update and delete documents in a dataset.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;
use Coze\Models\DocumentBase;
use Coze\Models\DocumentFormatType;

// Get configuration from environment variables
$token = getenv('COZE_API_TOKEN') ?: 'your_access_token';
$datasetId = getenv('COZE_DATASET_ID') ?: 'your_dataset_id';
$baseUrl = getenv('COZE_API_BASE') ?: CozeClient::BASE_URL_CN;

// Initialize the client
$client = new CozeClient(
    new TokenAuth($token),
    $baseUrl
);

echo "=== Document CRUD Example ===\n\n";

try {
    // 1. Create documents
    echo "1. Creating documents...\n";

    // Create a document from local file content
    $filePath = 'test1.pdf';
    if (!file_exists($filePath)) {
        die("错误：文件不存在");
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    echo $mimeType . "\n";
    if ($mimeType !== 'application/pdf') {
        die("错误：该文件不是有效的 PDF 文件，检测到的类型为：$mimeType");
    }

    $fileData = file_get_contents($filePath);
    // Note: Don't pre-encode with base64, buildLocalFile handles encoding internally
    $documentBases = [
        DocumentBase::buildLocalFile(
            'test1',
            $fileData,  // Raw file content, NOT base64 encoded
            'pdf'
        ),
    ];

    $createResp = $client->datasets->documents->create([
        'dataset_id' => (int) $datasetId,
        'document_bases' => $documentBases,
        'format_type' => DocumentFormatType::DOCUMENT,
    ]);

    $documentIds = [];
    if (isset($createResp['document_infos'])) {
        echo "   Created " . count($createResp['document_infos']) . " document(s)\n";
        foreach ($createResp['document_infos'] as $doc) {
            $documentIds[] = (int) $doc['document_id'];
            echo "   - {$doc['name']} (ID: {$doc['document_id']}, Status: {$doc['status']})\n";
        }
    } else {
        echo "   Response: " . json_encode($createResp, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    // 2. List documents
    echo "2. Listing documents...\n";
    $listResp = $client->datasets->documents->list([
        'dataset_id' => (int) $datasetId,
        'page' => 1,
        'size' => 20,
    ]);

    if (isset($listResp['document_infos'])) {
        echo "   Total documents: " . ($listResp['total'] ?? 0) . "\n";
        foreach ($listResp['document_infos'] as $doc) {
            $status = ['Processing', 'Completed', '', '', '', '', '', '', '', 'Failed'][$doc['status']] ?? 'Unknown';
            echo "   - {$doc['name']} (ID: {$doc['document_id']}, Status: {$status})\n";
        }
    }
    echo "\n";

    // 3. Update document (if we have created one)
    if (!empty($documentIds)) {
        echo "3. Updating document...\n";
        $updateResp = $client->datasets->documents->update([
            'document_id' => $documentIds[0],
            'document_name' => 'Updated Document Name',
        ]);
        echo "   Document updated successfully\n\n";

        // 4. Delete document
        echo "4. Deleting document...\n";
        $deleteResp = $client->datasets->documents->delete($documentIds);
        echo "   Document(s) deleted successfully\n\n";
    }

    echo "=== All operations completed successfully! ===\n";

} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
