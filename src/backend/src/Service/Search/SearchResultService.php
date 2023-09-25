<?php

namespace App\Service\Search;

use App\Model\Pagination;

class SearchResultService implements SearchResultServiceInterface
{
    private PaginatorInterface $paginator;

    public function __construct(
        PaginatorInterface $paginator
    ) {
        $this->paginator = $paginator;
    }

    /**
     * @inheritDoc
     */
    public function paginationResult(array $items, int $totalItems, int $page, int $size, ?string $class = null):
    Pagination {
        return $this->paginator->getResult(
            $items,
            $totalItems,
            $page,
            $size,
            $class
        );
    }
}