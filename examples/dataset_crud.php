<?php

/**
 * Coze PHP SDK - Dataset CRUD Example
 *
 * This example demonstrates how to create, list, update and delete datasets.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;
use Coze\Models\DocumentFormatType;

// Get configuration from environment variables
$token = getenv('COZE_API_TOKEN') ?: 'your_access_token';
$spaceId = getenv('COZE_SPACE_ID') ?: 'your_space_id';
$baseUrl = getenv('COZE_API_BASE') ?: CozeClient::BASE_URL_CN;

// Initialize the client
$client = new CozeClient(
    new TokenAuth($token),
    $baseUrl
);

echo "=== Dataset CRUD Example ===\n\n";

try {
    // 1. Create a new dataset
    echo "1. Creating dataset...\n";
    $createResp = $client->datasets->create([
        'name' => 'Test Dataset ' . date('Y-m-d H:i:s'),
        'space_id' => $spaceId,
        'format_type' => DocumentFormatType::DOCUMENT,
        'description' => 'A test knowledge base created via PHP SDK',
    ]);

    if (isset($createResp['data']['dataset_id'])) {
        $datasetId = $createResp['data']['dataset_id'];
        echo "   Created dataset ID: {$datasetId}\n\n";
    } else {
        echo "   Failed to create dataset\n";
        print_r($createResp);
        exit(1);
    }

    // 2. List datasets
    echo "2. Listing datasets...\n";
    $listResp = $client->datasets->list([
        'space_id' => $spaceId,
        'page_num' => 1,
        'page_size' => 10,
    ]);

    if (isset($listResp['data']['dataset_list'])) {
        echo "   Total datasets: " . ($listResp['data']['total_count'] ?? 0) . "\n";
        foreach ($listResp['data']['dataset_list'] as $ds) {
            echo "   - {$ds['name']} (ID: {$ds['dataset_id']})\n";
        }
    }
    echo "\n";

    // 3. Update dataset
    echo "3. Updating dataset...\n";
    $updateResp = $client->datasets->update($datasetId, [
        'name' => 'Updated Dataset ' . date('Y-m-d H:i:s'),
        'description' => 'Updated description via PHP SDK',
    ]);
    echo "   Dataset updated successfully\n\n";

    // 4. Delete dataset
    echo "4. Deleting dataset...\n";
    $deleteResp = $client->datasets->delete($datasetId);
    echo "   Dataset deleted successfully\n\n";

    echo "=== All operations completed successfully! ===\n";

} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
