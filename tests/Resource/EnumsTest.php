<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource;

use Oleksyuk\Apaleo\Tests\Support\Fixture;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class EnumsTest extends TestCase
{
    /** @param class-string<\BackedEnum> $enum */
    #[DataProvider('provideFromApiMapsKnownValuesAndFallsBackToUnknownCases')]
    public function testFromApiMapsKnownValuesAndFallsBackToUnknown(string $enum): void
    {
        self::assertTrue(method_exists($enum, 'fromApi'));

        foreach ($enum::cases() as $case) {
            self::assertSame($case, $enum::fromApi((string) $case->value));
        }

        $fallback = $enum::fromApi('NewValueFromApaleo');
        self::assertInstanceOf(\BackedEnum::class, $fallback);
        self::assertStringStartsWith('__', (string) $fallback->value);
    }

    /** @return iterable<string, array{class-string}> */
    public static function provideFromApiMapsKnownValuesAndFallsBackToUnknownCases(): iterable
    {
        foreach (Fixture::classesIn('Enum') as $name => [$class]) {
            if (method_exists($class, 'fromApi')) {
                yield $name => [$class];
            }
        }
    }
}
