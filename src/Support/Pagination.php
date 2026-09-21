<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Support;

/** Apaleo's page-size limit is platform-wide: verified identical on every Inventory list endpoint. */
final class Pagination
{
    public const int MAX_PAGE_SIZE = 500;

    /**
     * Apaleo rejects pageSize > 500 with a 422, and silently ignores 0 or negative
     * (returns everything unpaginated) — both caught here before any request is sent.
     *
     * @throws \InvalidArgumentException if $pageSize is outside [1, MAX_PAGE_SIZE]
     */
    public static function assertValidPageSize(?int $pageSize): void
    {
        if ($pageSize === null) {
            return;
        }

        if ($pageSize < 1 || $pageSize > self::MAX_PAGE_SIZE) {
            throw new \InvalidArgumentException(\sprintf('pageSize must be between 1 and %d, got %d.', self::MAX_PAGE_SIZE, $pageSize));
        }
    }
}
