<?php

namespace App\Service\Search;

use App\Common\SerializerHelper;
use App\Model\Pagination;

class Paginator implements PaginatorInterface
{
    use SearchResultTrait;

    /**
     * {@inheritDoc}
     */
    public function getResult(array $items, int $totalItems, int $page, int $size, string $class = null): Pagination
    {
        $pageCount = ceil($totalItems / $size);
        $itemResults = $class ?
            (new SerializerHelper())->toArrayObj($items, $class, []) :
            $this->modifyItemsBatch($items);

        return (new Pagination())
            ->setItems($itemResults)
            ->setTotalItems($totalItems)
            ->setTotalPage($pageCount)
            ->setPage($page)
            ->setSize($size)
            ->setFirst(1 === $page)
            ->setLast($pageCount == $page);
    }
}
