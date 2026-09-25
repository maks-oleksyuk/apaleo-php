<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Support\Pagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Pagination::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class PaginationTest extends TestCase
{
    public function testNullIsAllowed(): void
    {
        Pagination::assertValidPageSize(null);
        $this->addToAssertionCount(1);
    }

    public function testValidRangeIsAllowed(): void
    {
        Pagination::assertValidPageSize(1);
        Pagination::assertValidPageSize(500);
        $this->addToAssertionCount(1);
    }

    public function testAboveMaxThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Pagination::assertValidPageSize(501);
    }

    public function testZeroThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Pagination::assertValidPageSize(0);
    }

    public function testNegativeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Pagination::assertValidPageSize(-1);
    }
}
