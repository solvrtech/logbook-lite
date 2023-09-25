<?php

namespace App\Service\Search;

use App\Model\Pagination;

interface SearchResultServiceInterface
{
    /**
     * Generate pagination of the search result.
     *
     * @param array $items
     * @param int $totalItems
     * @param int $page
     * @param int $size
     * @param ?string $class
     *
     * @return Pagination
     */
    public function paginationResult(array $items, int $totalItems, int $page, int $size, ?string $class):
    Pagination;
}