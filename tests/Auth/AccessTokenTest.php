<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Auth;

use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\InMemoryTokenCache;
use Oleksyuk\Apaleo\Auth\SystemClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AccessToken::class)]
#[CoversClass(InMemoryTokenCache::class)]
#[CoversClass(SystemClock::class)]
final class AccessTokenTest extends TestCase
{
    public function testTokenExpiresThirtySecondsEarly(): void
    {
        $now = new \DateTimeImmutable('2026-01-15T10:00:00+00:00');

        self::assertFalse(new AccessToken('t', $now->modify('+31 seconds'))->isExpired($now));
        self::assertTrue(new AccessToken('t', $now->modify('+30 seconds'))->isExpired($now));
        self::assertTrue(new AccessToken('t', $now->modify('-1 hour'))->isExpired($now));
    }

    public function testTokenIsMeasuredAgainstTheCurrentTimeByDefault(): void
    {
        self::assertFalse(new AccessToken('t', new \DateTimeImmutable('+1 hour'))->isExpired());
    }

    public function testInMemoryCacheReturnsWhatWasStoredPerKey(): void
    {
        $cache = new InMemoryTokenCache();
        $token = new AccessToken('t', new \DateTimeImmutable('+1 hour'));

        self::assertNull($cache->get('a'));

        $cache->set('a', $token);

        self::assertSame($token, $cache->get('a'));
        self::assertNull($cache->get('b'));
    }

    public function testSystemClockReturnsTheCurrentTime(): void
    {
        $before = new \DateTimeImmutable();
        $now = new SystemClock()->now();

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $now);
    }
}
