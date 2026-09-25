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

    /**
     * Every POST that creates something takes an Idempotency-Key, so a retry after a timeout
     * can't book, charge or authorize twice. TransactionsRequest is a read (export/aggregate).
     */
    public function testEveryCreatingPostAcceptsAnIdempotencyKey(): void
    {
        $offenders = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__.'/../../src/Resource', \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            $source = (string) file_get_contents($file->getPathname());
            if (str_contains($source, 'Method::POST') && !str_contains($source, '$idempotencyKey') && $file->getBasename() !== 'TransactionsRequest.php') {
                $offenders[] = $file->getBasename();
            }
        }

        self::assertSame([], $offenders);
    }
}
