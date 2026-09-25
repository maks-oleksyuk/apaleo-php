<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource;

use Oleksyuk\Apaleo\Http\Request;
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
final class RequestsTest extends TestCase
{
    /** @param class-string<Request> $class */
    #[DataProvider('provideRequestDescribesAnApaleoEndpointCases')]
    public function testRequestDescribesAnApaleoEndpoint(string $class): void
    {
        $request = Fixture::object($class);

        self::assertStringStartsWith('/', $request->endpoint());
        self::assertNotSame('', $request->method()->value);
        self::assertSame($request->query(), $request->query());
        self::assertSame($request->headers(), $request->headers());
        self::assertSame($request->body(), $request->body());
    }

    /** @return iterable<string, array{class-string<Request>}> */
    public static function provideRequestDescribesAnApaleoEndpointCases(): iterable
    {
        foreach (Fixture::classesIn('Requests') as $name => [$class]) {
            if (is_subclass_of($class, Request::class)) {
                yield $name => [$class];
            }
        }
    }
}
