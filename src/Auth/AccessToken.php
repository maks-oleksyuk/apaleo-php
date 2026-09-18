<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

final class AccessToken
{
    public function __construct(
        public readonly string $value,
        public readonly \DateTimeImmutable $expiresAt,
    ) {
    }

    public function isExpired(): bool
    {
        // 30s safety margin so a token doesn't expire mid-flight.
        return $this->expiresAt <= new \DateTimeImmutable('+30 seconds');
    }
}
