<?php

/**
 * Coze PHP SDK - Workflow Stream Execution Example
 *
 * This example demonstrates how to execute a workflow using the Coze stream API.
 * The workflow stream API uses SSE (Server-Sent Events) with the following event types:
 *   - Message: Workflow output node data (may arrive in multiple chunks)
 *   - Error: Error information when execution fails
 *   - Interrupt: Workflow suspended, awaiting user input to resume
 *   - Done: Workflow execution completed
 *
 * API Endpoint: POST /v1/workflow/stream_run
 *
 * @see https://www.coze.cn/open/docs/developer_guides/workflow_run
 *
 * Equivalent curl:
 *   curl -X POST 'https://api.coze.cn/v1/workflow/stream_run' \
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

echo "=== Coze Workflow Stream Execution ===\n";
echo "Workflow ID: {$workflowId}\n";
echo "Input: {$inputText}\n";
echo str_repeat('-', 50) . "\n\n";

try {
    // Use the SDK's HTTP client to send streaming request
    $httpClient = $client->getHttpClient();

    $stream = $httpClient->postStream('/v1/workflow/stream_run', [
        'workflow_id' => $workflowId,
        'parameters' => [
            'input' => $inputText,
        ],
    ]);

    // Parse SSE events from the stream
    $buffer = '';

    while (!$stream->eof()) {
        $chunk = $stream->read(8192);
        if ($chunk === '') {
            continue;
        }

        $buffer .= $chunk;

        // Process complete events (separated by double newline)
        while (($eventEnd = strpos($buffer, "\n\n")) !== false) {
            $eventRaw = substr($buffer, 0, $eventEnd);
            $buffer = substr($buffer, $eventEnd + 2);

            // Parse event type and data from SSE lines
            $event = '';
            $data = '';
            foreach (explode("\n", $eventRaw) as $line) {
                $line = trim($line);
                if (strpos($line, 'event:') === 0) {
                    $event = trim(substr($line, 6));
                } elseif (strpos($line, 'data:') === 0) {
                    $data = trim(substr($line, 5));
                }
            }

            if (empty($event) && empty($data)) {
                continue;
            }

            // Handle different workflow event types
            switch ($event) {
                case 'Message':
                    // Workflow output data (from output node)
                    $decoded = json_decode($data, true);
                    if (is_array($decoded)) {
                        $content = $decoded['content'] ?? '';
                        $nodeTitle = $decoded['node_title'] ?? '';
                        $nodeSeqId = $decoded['node_seq_id'] ?? '';
                        $isFinish = $decoded['node_is_finish'] ?? false;

                        if (!empty($nodeTitle)) {
                            echo "[Node: {$nodeTitle} (seq: {$nodeSeqId})] ";
                        }
                        echo $content;

                        if ($isFinish) {
                            echo "\n[Node Finished]\n";
                        }
                    } else {
                        // Plain text output
                        echo $data;
                    }
                    break;

                case 'Error':
                    // Workflow execution error
                    $decoded = json_decode($data, true);
                    if (is_array($decoded)) {
                        $errCode = $decoded['error_code'] ?? 'unknown';
                        $errMsg = $decoded['error_message'] ?? $data;
                        echo "\n[Error] Code: {$errCode}, Message: {$errMsg}\n";
                    } else {
                        echo "\n[Error] {$data}\n";
                    }
                    break;

                case 'Interrupt':
                    // Workflow suspended, awaiting resume
                    $decoded = json_decode($data, true);
                    if (is_array($decoded)) {
                        $interruptType = $decoded['interrupt_type'] ?? 'unknown';
                        $nodeTitle = $decoded['node_title'] ?? '';
                        echo "\n[Interrupt] Type: {$interruptType}, Node: {$nodeTitle}\n";
                        echo "  Data: " . json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
                    } else {
                        echo "\n[Interrupt] {$data}\n";
                    }
                    break;

                case 'Done':
                    echo "\n" . str_repeat('-', 50) . "\n";
                    echo "[Workflow Done]\n";
                    break;

                default:
                    // Unknown event type - print raw data for debugging
                    echo "[{$event}] {$data}\n";
                    break;
            }
        }
    }

    // Process remaining buffer
    if (!empty(trim($buffer))) {
        echo "[Remaining] {$buffer}\n";
    }

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
