<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;

/** Hands out the same valid token every time, so tests never hit the OAuth endpoint. */
final class FakeTokenProvider implements TokenProvider
{
    public function getToken(bool $forceRefresh = false): AccessToken
    {
        return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
    }
}
