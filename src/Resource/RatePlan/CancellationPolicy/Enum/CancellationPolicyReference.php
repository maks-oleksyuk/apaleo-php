<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Enum;

enum CancellationPolicyReference: string
{
    case PriorToArrival = 'PriorToArrival';
    case AfterBooking = 'AfterBooking';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
