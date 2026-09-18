<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

final class InMemoryTokenCache implements TokenCache
{
    /** @var array<string, AccessToken> */
    private array $tokens = [];

    public function get(string $key): ?AccessToken
    {
        return $this->tokens[$key] ?? null;
    }

    public function set(string $key, AccessToken $token): void
    {
        $this->tokens[$key] = $token;
    }
}
