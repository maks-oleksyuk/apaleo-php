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

        $result[0] = 'b';
    }

    public function testArrayAccessCannotUnsetItems(): void
    {
        $result = new PaginatedResult(items: ['a'], totalCount: 1);

        $this->expectException(\LogicException::class);

        unset($result[0]);
    }
}
