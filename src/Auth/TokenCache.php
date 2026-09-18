<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

interface TokenCache
{
    public function get(string $key): ?AccessToken;

    public function set(string $key, AccessToken $token): void;
}
