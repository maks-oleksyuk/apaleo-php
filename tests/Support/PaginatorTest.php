<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Paginator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Paginator::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class PaginatorTest extends TestCase
{
    public function testWalksAllPagesUntilTotalCountIsReached(): void
    {
        $pages = [
            1 => new PaginatedResult(items: ['a', 'b'], totalCount: 5),
            2 => new PaginatedResult(items: ['c', 'd'], totalCount: 5),
            3 => new PaginatedResult(items: ['e'], totalCount: 5),
        ];
        $calls = [];

        $items = iterator_to_array(Paginator::all(static function (int $pageNumber) use ($pages, &$calls): PaginatedResult {
            $calls[] = $pageNumber;

            return $pages[$pageNumber];
        }), false);

        self::assertSame(['a', 'b', 'c', 'd', 'e'], $items);
        self::assertSame([1, 2, 3], $calls);
    }

    public function testSinglePageStopsAfterOneFetch(): void
    {
        $calls = 0;

        $items = iterator_to_array(Paginator::all(static function () use (&$calls): PaginatedResult {
            ++$calls;

            return new PaginatedResult(items: ['a'], totalCount: 1);
        }), false);

        self::assertSame(['a'], $items);
        self::assertSame(1, $calls);
    }

    public function testEmptyFirstPageStopsImmediately(): void
    {
        $calls = 0;

        $items = iterator_to_array(Paginator::all(static function () use (&$calls): PaginatedResult {
            ++$calls;

            return new PaginatedResult(items: [], totalCount: 0);
        }), false);

        self::assertSame([], $items);
        self::assertSame(1, $calls);
    }

    public function testMissingCountKeepsPagingUntilAnEmptyPage(): void
    {
        $pages = [
            1 => new PaginatedResult(items: ['a', 'b'], totalCount: 0),
            2 => new PaginatedResult(items: ['c'], totalCount: 0),
            3 => new PaginatedResult(items: [], totalCount: 0),
        ];

        $items = iterator_to_array(Paginator::all(static fn (int $page): PaginatedResult => $pages[$page]), false);

        self::assertSame(['a', 'b', 'c'], $items);
    }
}
