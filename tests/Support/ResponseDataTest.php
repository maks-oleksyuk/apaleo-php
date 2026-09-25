<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Support\ResponseData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ResponseData::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
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

    #[TestWith([''])]
    #[TestWith(['   '])]
    #[TestWith(['tomorrow'])]
    #[TestWith(['2026-09-22'])]
    public function testDateTimeRejectsNonIsoValues(string $value): void
    {
        $this->expectException(ApaleoUnexpectedResponseException::class);

        ResponseData::dateTime(['d' => $value], 'd');
    }

    public function testNullableDateTimeIsNullForBlankButParsesIso(): void
    {
        self::assertNull(ResponseData::nullableDateTime(['d' => ''], 'd'));
        self::assertSame('2026-09-22T10:00:00+02:00', ResponseData::nullableDateTime(['d' => '2026-09-22T10:00:00+02:00'], 'd')?->format(DATE_ATOM));
    }

    public function testNullableNestedMapsPresentObjectsOnly(): void
    {
        $map = static fn (array $value): mixed => $value['id'] ?? null;
        $data = ['obj' => ['id' => 'X'], 'empty' => [], 'null' => null];

        self::assertSame('X', ResponseData::nullableNested($data, 'obj', $map));
        self::assertNull(ResponseData::nullableNested($data, 'empty', $map));
        self::assertNull(ResponseData::nullableNested($data, 'null', $map));
        self::assertNull(ResponseData::nullableNested($data, 'missing', $map));
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

    public function testStringRejectsMissingOrNonStringValues(): void
    {
        $this->expectException(ApaleoUnexpectedResponseException::class);

        ResponseData::string(['name' => 5], 'name');
    }

    public function testIntAcceptsIntsAndNumericStringsButNothingElse(): void
    {
        self::assertSame(3, ResponseData::int(['n' => 3], 'n'));
        self::assertSame(3, ResponseData::int(['n' => '3'], 'n'));

        $this->expectException(ApaleoUnexpectedResponseException::class);
        ResponseData::int(['n' => 3.5], 'n');
    }

    public function testFloatAcceptsIntsAndFloatsButNothingElse(): void
    {
        self::assertSame(2.0, ResponseData::float(['n' => 2], 'n'));
        self::assertSame(2.5, ResponseData::float(['n' => 2.5], 'n'));

        $this->expectException(ApaleoUnexpectedResponseException::class);
        ResponseData::float(['n' => '2.5'], 'n');
    }

    public function testNullableFloatIsNullWhenAbsentOrNotANumber(): void
    {
        self::assertNull(ResponseData::nullableFloat([], 'n'));
        self::assertSame(1.5, ResponseData::nullableFloat(['n' => 1.5], 'n'));
        self::assertNull(ResponseData::nullableFloat(['n' => 'x'], 'n'));
    }

    public function testBoolFallsBackToTheDefaultForNonBooleans(): void
    {
        self::assertTrue(ResponseData::bool(['b' => true], 'b'));
        self::assertFalse(ResponseData::bool(['b' => 'yes'], 'b'));
        self::assertTrue(ResponseData::bool([], 'b', true));
    }

    public function testNullableIntIsNullWhenAbsentAndRejectsGarbage(): void
    {
        self::assertNull(ResponseData::nullableInt([], 'n'));
        self::assertSame(7, ResponseData::nullableInt(['n' => '7'], 'n'));

        $this->expectException(ApaleoUnexpectedResponseException::class);
        ResponseData::nullableInt(['n' => 'x'], 'n');
    }

    public function testIntListDropsNonIntItemsAndTreatsNonArraysAsEmpty(): void
    {
        self::assertSame([1, 2], ResponseData::intList(['n' => [1, 'x', 2, 3.5]], 'n'));
        self::assertSame([], ResponseData::intList(['n' => 'x'], 'n'));
    }

    public function testNestedListKeepsOnlyObjectsWithStringKeys(): void
    {
        self::assertSame([['a' => 1]], ResponseData::nestedList(['l' => [['a' => 1, 0 => 'dropped'], 'scalar']], 'l'));
        self::assertSame([], ResponseData::nestedList(['l' => 'x'], 'l'));
    }

    public function testDateTimeRejectsAnUnparsableTimestamp(): void
    {
        $this->expectException(ApaleoUnexpectedResponseException::class);

        ResponseData::dateTime(['at' => '2026-13-45T99:99:99'], 'at');
    }

    public function testNullableStringIsNullForNonStrings(): void
    {
        self::assertNull(ResponseData::nullableString(['s' => 5], 's'));
        self::assertNull(ResponseData::nullableString([], 's'));
    }

    public function testStringListRejectsANonList(): void
    {
        $this->expectException(ApaleoUnexpectedResponseException::class);

        ResponseData::stringList(['l' => 'x'], 'l');
    }

    public function testLocalizedTextIsEmptyForNonTextValues(): void
    {
        self::assertSame([], ResponseData::localizedText(['t' => 5], 't'));
        self::assertSame(['en' => 'Hi'], ResponseData::localizedText(['t' => ['en' => ' Hi ', 0 => 'dropped']], 't'));
    }
}
