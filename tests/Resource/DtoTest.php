<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource;

use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
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
final class DtoTest extends TestCase
{
    /** @param class-string $class */
    #[DataProvider('provideFromArrayBuildsTheDtoCases')]
    public function testFromArrayBuildsTheDto(string $class): void
    {
        $dto = $class::fromArray(Fixture::response($class));

        self::assertInstanceOf($class, $dto);

        if (method_exists($dto, 'toArray')) {
            self::assertIsArray($dto->toArray());
        }
    }

    /** @return iterable<string, array{class-string}> */
    public static function provideFromArrayBuildsTheDtoCases(): iterable
    {
        foreach (Fixture::classesIn('DTO') as $name => [$class]) {
            if (method_exists($class, 'fromArray') && self::mirrorsConstructor($class)) {
                yield $name => [$class];
            }
        }
    }

    /** @param class-string $class */
    #[DataProvider('provideToArrayBuildsTheRequestPayloadCases')]
    public function testToArrayBuildsTheRequestPayload(string $class): void
    {
        $dto = Fixture::object($class);
        self::assertTrue(method_exists($dto, 'toArray'));
        $payload = $dto->toArray();

        self::assertNotSame([], $payload);
    }

    /** @return iterable<string, array{class-string}> */
    public static function provideToArrayBuildsTheRequestPayloadCases(): iterable
    {
        foreach (Fixture::classesIn('DTO') as $name => [$class]) {
            if (!method_exists($class, 'fromArray') && method_exists($class, 'toArray')) {
                yield $name => [$class];
            }
        }
    }

    /**
     * DTOs that flatten or rename nested JSON (`status.condition` -> `$condition`) cannot be fed a
     * constructor-shaped fixture; the resource tests cover those with hand-written responses.
     *
     * @param class-string $class
     */
    private static function mirrorsConstructor(string $class): bool
    {
        try {
            $class::fromArray(Fixture::response($class));
        } catch (ApaleoUnexpectedResponseException) {
            return false;
        }

        return true;
    }
}
