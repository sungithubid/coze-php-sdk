<?php

declare(strict_types=1);

namespace Coze\Datasets;

use Coze\Exceptions\CozeException;
use Coze\Http\HttpClient;
use Coze\Models\Dataset;

/**
 * Datasets API client for managing knowledge bases
 */
class DatasetsClient
{
    /** @var HttpClient */
    private $httpClient;

    /** @var DocumentsClient */
    public $documents;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->documents = new DocumentsClient($httpClient);
    }

    /**
     * Create a new dataset (knowledge base)
     *
     * @param array $request Create request
     *   - name: string (required) - Dataset name
     *   - space_id: string (required) - Space ID
     *   - format_type: int (required) - 0: Document, 1: Spreadsheet, 2: Image
     *   - description: string (optional) - Dataset description
     *   - file_id: string (optional) - Icon file ID
     *
     * @return array Response with dataset_id
     * @throws CozeException
     */
    public function create(array $request): array
    {
        return $this->httpClient->post('/v1/datasets', $request);
    }

    /**
     * List datasets in a space
     *
     * @param array $request List request
     *   - space_id: string (required) - Space ID
     *   - name: string (optional) - Filter by name
     *   - format_type: int (optional) - Filter by format type
     *   - page_num: int (optional) - Page number, default 1
     *   - page_size: int (optional) - Page size, default 10
     *
     * @return array Response with dataset_list and total_count
     * @throws CozeException
     */
    public function list(array $request): array
    {
        $query = [
            'space_id' => $request['space_id'],
        ];

        if (isset($request['name'])) {
            $query['name'] = $request['name'];
        }
        if (isset($request['format_type'])) {
            $query['format_type'] = $request['format_type'];
        }
        if (isset($request['page_num'])) {
            $query['page_num'] = $request['page_num'];
        }
        if (isset($request['page_size'])) {
            $query['page_size'] = $request['page_size'];
        }

        return $this->httpClient->get('/v1/datasets', $query);
    }

    /**
     * Update a dataset
     *
     * @param string $datasetId Dataset ID
     * @param array $request Update request
     *   - name: string (required) - New name
     *   - description: string (optional) - New description
     *   - file_id: string (optional) - New icon file ID
     *
     * @return array Response
     * @throws CozeException
     */
    public function update(string $datasetId, array $request): array
    {
        return $this->httpClient->put('/v1/datasets/' . $datasetId, $request);
    }

    /**
     * Delete a dataset
     *
     * @param string $datasetId Dataset ID
     * @return array Response
     * @throws CozeException
     */
    public function delete(string $datasetId): array
    {
        return $this->httpClient->delete('/v1/datasets/' . $datasetId);
    }

    /**
     * Get processing progress of documents
     *
     * @param string $datasetId Dataset ID
     * @param array $documentIds Document IDs
     * @return array Response with processing progress
     * @throws CozeException
     */
    public function process(string $datasetId, array $documentIds): array
    {
        return $this->httpClient->post('/v1/datasets/' . $datasetId . '/process', [
            'document_ids' => $documentIds,
        ]);
    }
}
