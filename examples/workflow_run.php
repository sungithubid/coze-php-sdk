<?php

/**
 * Coze PHP SDK - Workflow Execution Example (Non-Streaming)
 *
 * This example demonstrates how to execute a workflow using the Coze non-streaming API.
 * The response is returned as a complete JSON object after the workflow finishes.
 *
 * API Endpoint: POST /v1/workflow/run
 *
 * @see https://www.coze.cn/open/docs/developer_guides/workflow_run
 *
 * Equivalent curl:
 *   curl -X POST 'https://api.coze.cn/v1/workflow/run' \
 *     -H "Authorization: Bearer your_token" \
 *     -H "Content-Type: application/json" \
 *     -d '{
 *       "workflow_id": "your_workflow_id",
 *       "parameters": {
 *         "input": "your input text"
 *       }
 *     }'
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coze\CozeClient;
use Coze\Auth\TokenAuth;

// Get configuration from environment variables
$token = getenv('COZE_API_TOKEN') ?: 'your_access_token';
$workflowId = getenv('COZE_WORKFLOW_ID') ?: 'your_workflow_id';
$baseUrl = getenv('COZE_API_BASE') ?: CozeClient::BASE_URL_CN;

// Workflow input parameters
$inputText = '帮我解析如下微信文章内容\nhttps://mp.weixin.qq.com/s/xxx';

// Initialize the client
$client = new CozeClient(
    new TokenAuth($token),
    $baseUrl
);

echo "=== Coze Workflow Execution (Non-Streaming) ===\n";
echo "Workflow ID: {$workflowId}\n";
echo "Input: {$inputText}\n";
echo str_repeat('-', 50) . "\n\n";

try {
    // Use the SDK's HTTP client to send non-streaming request
    $httpClient = $client->getHttpClient();

    $response = $httpClient->post('/v1/workflow/run', [
        'workflow_id' => $workflowId,
        'parameters' => [
            'input' => $inputText,
        ],
    ]);

    // Response structure:
    // {
    //   "code": 0,
    //   "msg": "success",
    //   "data": "workflow output content",
    //   "debug_url": "https://...",
    //   "cost": "0.005",
    //   "token": 1234
    // }

    echo "[Response]\n";

    if (isset($response['data'])) {
        echo "Output: " . $response['data'] . "\n";
    }

    if (isset($response['cost'])) {
        echo "Cost: " . $response['cost'] . "\n";
    }

    if (isset($response['token'])) {
        echo "Token: " . $response['token'] . "\n";
    }

    if (isset($response['debug_url'])) {
        echo "Debug URL: " . $response['debug_url'] . "\n";
    }

    echo "\n" . str_repeat('-', 50) . "\n";
    echo "[Workflow Done]\n";

} catch (\Coze\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getErrorMessage() . " (Code: " . $e->getErrorCode() . ")\n";
    if ($e->getLogId()) {
        echo "Log ID: " . $e->getLogId() . "\n";
    }
} catch (\Coze\Exceptions\CozeException $e) {
    echo "SDK Error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
