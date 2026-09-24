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

    public function testStringsAreTrimmed(): void
    {
        $data = [
            's' => " Breakfast \t",
            'n' => 'Buffet  ',
            'blank' => '   ',
            'list' => [' a', 'b '],
            'loc' => ['en' => 'Double Room ', 'de' => ' Doppelzimmer'],
            'plain' => ' Single ',
        ];

        self::assertSame('Breakfast', ResponseData::string($data, 's'));
        self::assertSame('Buffet', ResponseData::nullableString($data, 'n'));
        self::assertNull(ResponseData::nullableString($data, 'blank'));
        self::assertSame('', ResponseData::string($data, 'blank'));
        self::assertSame(['a', 'b'], ResponseData::stringList($data, 'list'));
        self::assertSame(['a', 'b'], ResponseData::stringListOrEmpty($data, 'list'));
        self::assertSame(['en' => 'Double Room', 'de' => 'Doppelzimmer'], ResponseData::localizedText($data, 'loc'));
        self::assertSame(['default' => 'Single'], ResponseData::localizedText($data, 'plain'));
    }
}
