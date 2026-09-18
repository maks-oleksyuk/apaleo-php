<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestTest extends TestCase
{
    public function testMethodThrowsClearErrorWhenNotDeclared(): void
    {
        $request = new class extends Request {
            public function endpoint(): string
            {
                return '/x';
            }
        };

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is missing an HTTP method');

        $request->method();
    }

    public function testMethodReturnsDeclaredValue(): void
    {
        $request = new class extends Request {
            protected Method $method = Method::POST;

            public function endpoint(): string
            {
                return '/x';
            }
        };

        self::assertSame(Method::POST, $request->method());
    }
}
