<?php

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\CountWalker;

final class Paginator
{
    public const PAGE_SIZE = 10;

    private int $currentPage = 1;
    private int $numResults = 0;
    private ?DoctrinePaginator $paginator = null;

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly int $pageSize = self::PAGE_SIZE,
    ) {
    }

    public function paginate(int $page = 1): self
    {
        $clone = clone $this;
        $clone->currentPage = max(1, $page);
        $firstResult = ($clone->currentPage - 1) * $this->pageSize;

        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        if (0 === \count($this->queryBuilder->getDQLPart('join'))) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $clone->paginator = new DoctrinePaginator($query, true);
        $clone->paginator->setUseOutputWalkers(false);
        $clone->numResults = $clone->paginator->count();

        return $clone;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return (int) ceil($this->numResults / $this->pageSize);
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    public function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage + 1);
    }

    public function hasToPaginate(): bool
    {
        return $this->numResults > $this->pageSize;
    }

    public function getNumResults(): int
    {
        return $this->numResults;
    }

    /**
     * @return iterable<mixed>
     */
    public function getResults(): iterable
    {
        return $this->paginator ?? [];
    }
}
