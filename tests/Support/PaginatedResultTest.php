<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Support\PaginatedResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PaginatedResult::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class PaginatedResultTest extends TestCase
{
    public function testFromResponseMapsItemsAndReadsCount(): void
    {
        $result = PaginatedResult::fromResponse(
            ['things' => [['n' => 1], ['n' => 2]], 'count' => 7],
            'things',
            static fn (array $row): array => $row,
        );

        self::assertSame([['n' => 1], ['n' => 2]], $result->items);
        self::assertSame(7, $result->totalCount);
    }

    public function testFromResponseDefaultsTotalCountToZeroWhenMissing(): void
    {
        $result = PaginatedResult::fromResponse(['things' => []], 'things', static fn (array $row): array => $row);

        self::assertSame([], $result->items);
        self::assertSame(0, $result->totalCount);
    }

    public function testCountReflectsThisPageNotTotalCount(): void
    {
        $result = new PaginatedResult(items: ['a', 'b'], totalCount: 42);

        self::assertCount(2, $result);
        self::assertSame(42, $result->totalCount);
    }

    public function testIteratesOverItems(): void
    {
        $result = new PaginatedResult(items: ['a', 'b', 'c'], totalCount: 3);

        self::assertSame(['a', 'b', 'c'], iterator_to_array($result));
    }

    public function testArrayAccessReadsItems(): void
    {
        $result = new PaginatedResult(items: ['a', 'b'], totalCount: 2);

        self::assertSame('a', $result[0]);
        self::assertTrue(isset($result[1]));
        self::assertFalse(isset($result[5]));
    }

    public function testArrayAccessIsReadOnly(): void
    {
        $result = new PaginatedResult(items: ['a'], totalCount: 1);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs(PaginatedResult::class.' is read-only.');

        $result[0] = 'b';
    }

    public function testArrayAccessCannotUnsetItems(): void
    {
        $result = new PaginatedResult(items: ['a'], totalCount: 1);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs(PaginatedResult::class.' is read-only.');

        unset($result[0]);
    }
}
