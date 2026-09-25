<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Auth;

use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\Psr16TokenCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @internal
 */
#[CoversClass(Psr16TokenCache::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class Psr16TokenCacheTest extends TestCase
{
    public function testSetThenGetRoundTripsTheToken(): void
    {
        $cache = $this->inMemoryPsr16Cache();
        $tokenCache = new Psr16TokenCache($cache);
        $token = new AccessToken('abc123', new \DateTimeImmutable('+1 hour'));

        $tokenCache->set('key', $token);
        $result = $tokenCache->get('key');

        self::assertNotNull($result);
        self::assertSame('abc123', $result->value);
        self::assertSame($token->expiresAt->getTimestamp(), $result->expiresAt->getTimestamp());
    }

    public function testGetReturnsNullWhenNothingCached(): void
    {
        $tokenCache = new Psr16TokenCache($this->inMemoryPsr16Cache());

        self::assertNull($tokenCache->get('missing'));
    }

    public function testGetReturnsNullOnMalformedCachedValue(): void
    {
        $cache = $this->inMemoryPsr16Cache();
        $cache->set('key', 'not-an-array');

        self::assertNull(new Psr16TokenCache($cache)->get('key'));
    }

    public function testGetReturnsNullWhenTheCachedTimestampIsUnparsable(): void
    {
        $cache = $this->inMemoryPsr16Cache();
        $cache->set('key', ['value' => 'abc', 'expiresAt' => 'not a date at all']);

        self::assertNull(new Psr16TokenCache($cache)->get('key'));
    }

    public function testSetPassesTtlMatchingTokenExpiry(): void
    {
        $cache = new InMemoryPsr16Cache();
        $tokenCache = new Psr16TokenCache($cache);

        $tokenCache->set('key', new AccessToken('abc123', new \DateTimeImmutable('+120 seconds')));

        self::assertNotNull($cache->lastTtl);
        self::assertGreaterThan(0, $cache->lastTtl);
        self::assertLessThanOrEqual(120, $cache->lastTtl);
    }

    private function inMemoryPsr16Cache(): CacheInterface
    {
        return new InMemoryPsr16Cache();
    }
}

/**
 * @internal
 */
final class InMemoryPsr16Cache implements CacheInterface
{
    public ?int $lastTtl = null;

    /** @var array<string, mixed> */
    private array $values = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->values[$key] = $value;
        $this->lastTtl = \is_int($ttl) ? $ttl : null;

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->values[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->values = [];

        return true;
    }

    /**
     * @param iterable<string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return [];
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        return true;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->values[$key]);
    }
}
