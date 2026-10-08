<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\Enum;

enum AvailabilityMode: string
{
    case Arrival = 'Arrival';
    case Departure = 'Departure';
    case Daily = 'Daily';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
