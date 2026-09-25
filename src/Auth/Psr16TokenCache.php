<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Persists the access token across requests via any PSR-16 cache (Redis, APCu, filesystem, ...).
 * Without this, InMemoryTokenCache starts empty on every PHP-FPM request, so every page load
 * fetches a fresh token from identity.apaleo.com instead of reusing one for its ~1h lifetime.
 */
final readonly class Psr16TokenCache implements TokenCache
{
    public function __construct(
        private CacheInterface $cache,
        private ClockInterface $clock = new SystemClock(),
    ) {}

    public function get(string $key): ?AccessToken
    {
        $stored = $this->cache->get($key);
        if (!\is_array($stored) || !\is_string($stored['value'] ?? null) || !\is_string($stored['expiresAt'] ?? null)) {
            return null;
        }

        try {
            return new AccessToken($stored['value'], new \DateTimeImmutable($stored['expiresAt']));
        } catch (\Exception) {
            return null;
        }
    }

    public function set(string $key, AccessToken $token): void
    {
        $ttl = max(1, $token->expiresAt->getTimestamp() - $this->clock->now()->getTimestamp());

        $this->cache->set($key, [
            'value' => $token->value,
            'expiresAt' => $token->expiresAt->format(DATE_ATOM),
        ], $ttl);
    }
}
