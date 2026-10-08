<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Enum;

enum CancellationPolicyReference: string
{
    case PriorToArrival = 'PriorToArrival';
    case AfterBooking = 'AfterBooking';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
