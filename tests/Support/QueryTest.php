<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Support\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Query::class)]
final class QueryTest extends TestCase
{
    public function testEmptyListIsOmitted(): void
    {
        self::assertNull(Query::csv([]));
    }

    public function testZeroIsKept(): void
    {
        self::assertSame('0', Query::csv([0]));
        self::assertSame('0', Query::csv(['0']));
    }

    public function testValuesAndEnumsAreCommaSeparated(): void
    {
        self::assertSame('0,5', Query::csv([0, 5]));
        self::assertSame('Confirmed,InHouse', Query::csv([ReservationStatus::Confirmed, ReservationStatus::InHouse]));
    }
}
