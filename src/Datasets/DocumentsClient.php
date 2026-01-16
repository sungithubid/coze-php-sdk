<?php

declare(strict_types=1);

namespace Coze\Datasets;

use Coze\Exceptions\CozeException;
use Coze\Http\HttpClient;
use Coze\Models\Document;
use Coze\Models\DocumentBase;

/**
 * Documents API client for managing knowledge base files
 */
class DocumentsClient
{
    /** @var HttpClient */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create documents in a dataset
     *
     * @param array $request Create request
     *   - dataset_id: int (required) - Dataset ID
     *   - document_bases: array (required) - Array of DocumentBase or arrays
     *   - chunk_strategy: array (optional) - Chunking strategy
     *   - format_type: int (optional) - 0: Document, 2: Image
     *
     * @return array Response with document_infos
     * @throws CozeException
     */
    public function create(array $request): array
    {
        // Convert DocumentBase objects to arrays
        if (isset($request['document_bases'])) {
            $request['document_bases'] = array_map(function ($base) {
                if ($base instanceof DocumentBase) {
                    return $base->toArray();
                }
                return $base;
            }, $request['document_bases']);
        }

        return $this->httpClient->post('/open_api/knowledge/document/create', $request, [
            'headers' => ['Agw-Js-Conv' => 'str'],
        ]);
    }

    /**
     * List documents in a dataset
     *
     * @param array $request List request
     *   - dataset_id: int (required) - Dataset ID
     *   - page: int (optional) - Page number, default 1
     *   - size: int (optional) - Page size, default 20
     *
     * @return array Response with document_infos and total
     * @throws CozeException
     */
    public function list(array $request): array
    {
        return $this->httpClient->post('/open_api/knowledge/document/list', $request, [
            'headers' => ['Agw-Js-Conv' => 'str'],
        ]);
    }

    /**
     * Update a document
     *
     * @param array $request Update request
     *   - document_id: int (required) - Document ID
     *   - document_name: string (optional) - New name
     *   - update_rule: array (optional) - Update rule for web pages
     *
     * @return array Response
     * @throws CozeException
     */
    public function update(array $request): array
    {
        return $this->httpClient->post('/open_api/knowledge/document/update', $request, [
            'headers' => ['Agw-Js-Conv' => 'str'],
        ]);
    }

    /**
     * Delete documents
     *
     * @param array $documentIds Array of document IDs (integers)
     * @return array Response
     * @throws CozeException
     */
    public function delete(array $documentIds): array
    {
        return $this->httpClient->post('/open_api/knowledge/document/delete', [
            'document_ids' => $documentIds,
        ], [
            'headers' => ['Agw-Js-Conv' => 'str'],
        ]);
    }
}
