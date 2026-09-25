<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Request::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class RequestTest extends TestCase
{
    public function testMethodReturnsDeclaredValue(): void
    {
        $request = new readonly class extends Request {
            public function method(): Method
            {
                return Method::POST;
            }

            public function endpoint(): string
            {
                return '/x';
            }
        };

        self::assertSame(Method::POST, $request->method());
    }

    public function testDefaultsAreAnEmptyJsonGet(): void
    {
        $request = new readonly class extends Request {
            public function method(): Method
            {
                return Method::GET;
            }

            public function endpoint(): string
            {
                return '/x';
            }
        };

        self::assertSame([], $request->query());
        self::assertSame('application/json', $request->accept());
        self::assertSame([], $request->headers());
        self::assertNull($request->body());
    }
}
