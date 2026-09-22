<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

final readonly class AccessToken
{
    public function __construct(
        public string $value,
        public \DateTimeImmutable $expiresAt,
    ) {}

    public function isExpired(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        // 30s safety margin so a token doesn't expire mid-flight.
        return $this->expiresAt <= $now->modify('+30 seconds');
    }
}
