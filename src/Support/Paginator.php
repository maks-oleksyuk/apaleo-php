<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

/**
 * Walks every page of a list() call and yields items lazily, using the totalCount Apaleo already returns.
 * Pass a pageSize up to Pagination::MAX_PAGE_SIZE (500) in the fetcher — Apaleo rejects anything higher
 * with a 422, and list()/count() already validate this via Pagination::assertValidPageSize().
 */
final class Paginator
{
    /**
     * @template T
     *
     * @param callable(int $pageNumber): PaginatedResult<T> $fetchPage fetches one page, 1-indexed
     *
     * @return \Generator<int, T>
     */
    public static function all(callable $fetchPage): \Generator
    {
        $pageNumber = 1;
        $seen = 0;

        do {
            $page = $fetchPage($pageNumber);
            foreach ($page as $item) {
                yield $item;
            }

            $seen += \count($page);
            ++$pageNumber;
        } while (\count($page) > 0 && $seen < $page->totalCount);
    }
}
