<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Auth;

use Psr\Clock\ClockInterface;

/** Default clock, so the SDK doesn't need a PSR-20 implementation package installed. */
final readonly class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
