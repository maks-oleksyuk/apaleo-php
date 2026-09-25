<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Exception;

use Oleksyuk\Apaleo\Exception\ApaleoException;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Exception\ApaleoRateLimitException;
use Oleksyuk\Apaleo\Exception\ApaleoValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ApaleoException::class)]
#[CoversClass(ApaleoRateLimitException::class)]
#[CoversClass(ApaleoValidationException::class)]
#[UsesClass(ApaleoNotFoundException::class)]
final class ExceptionsTest extends TestCase
{
    public function testBaseExceptionKeepsTheApiErrorDetails(): void
    {
        $previous = new \RuntimeException('root cause');
        $exception = new ApaleoNotFoundException('gone', 404, 'https://apaleo/errors/not-found', ['detail' => 'gone'], $previous);

        self::assertSame(404, $exception->statusCode);
        self::assertSame(404, $exception->getCode());
        self::assertSame('https://apaleo/errors/not-found', $exception->apaleoErrorType);
        self::assertSame(['detail' => 'gone'], $exception->rawResponse);
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testRateLimitExceptionCarriesRetryAfter(): void
    {
        $exception = new ApaleoRateLimitException('slow down', 429, retryAfterSeconds: 30);

        self::assertSame(30, $exception->retryAfterSeconds);
        self::assertSame(429, $exception->statusCode);
    }

    public function testRetryAfterParsesDeltaSecondsAndHttpDates(): void
    {
        self::assertSame(120, ApaleoRateLimitException::parseRetryAfter('120'));

        $seconds = ApaleoRateLimitException::parseRetryAfter(new \DateTimeImmutable('+2 minutes')->format('D, d M Y H:i:s \G\M\T'));
        self::assertGreaterThan(0, $seconds);
        self::assertLessThanOrEqual(120, $seconds);

        self::assertSame(0, ApaleoRateLimitException::parseRetryAfter('Wed, 21 Oct 2015 07:28:00 GMT'));
    }

    public function testRetryAfterIsNullWhenAbsentOrUnparsable(): void
    {
        self::assertNull(ApaleoRateLimitException::parseRetryAfter(''));
        self::assertNull(ApaleoRateLimitException::parseRetryAfter('soon'));
    }

    public function testValidationExceptionCarriesTheIndividualMessages(): void
    {
        $exception = new ApaleoValidationException('Code: required.', 422, messages: ['Code: required.']);

        self::assertSame(['Code: required.'], $exception->messages);
        self::assertSame(422, $exception->statusCode);
    }
}
