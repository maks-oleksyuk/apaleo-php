<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Support\ResponseData;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResponseDataTest extends TestCase
{
    public function testDateIsMidnightUtcRegardlessOfDefaultTimezone(): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set('America/New_York');

        try {
            $date = ResponseData::date(['d' => '2026-09-22'], 'd');
        } finally {
            date_default_timezone_set($previous);
        }

        self::assertSame('2026-09-22T00:00:00+00:00', $date->format(DATE_ATOM));
    }

    public function testNullableDateIsNullWhenAbsent(): void
    {
        self::assertNull(ResponseData::nullableDate([], 'd'));
    }

    public function testDateRejectsANonDateValue(): void
    {
        $this->expectException(ApaleoUnexpectedResponseException::class);

        ResponseData::date(['d' => '2026-02-30'], 'd');
    }
}
