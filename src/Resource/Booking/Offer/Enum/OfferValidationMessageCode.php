<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Enum;

enum OfferValidationMessageCode: string
{
    case UnitGroupFullyBooked = 'UnitGroupFullyBooked';
    case UnitGroupCapacityExceeded = 'UnitGroupCapacityExceeded';
    case RatePlanRestrictionsViolated = 'RatePlanRestrictionsViolated';
    case RatePlanSurchargesNotSet = 'RatePlanSurchargesNotSet';
    case RateRestrictionsViolated = 'RateRestrictionsViolated';
    case RatePlanChannelNotSet = 'RatePlanChannelNotSet';
    case RatesNotSet = 'RatesNotSet';
    case BlockFullyBooked = 'BlockFullyBooked';
    case IncludedServicesAmountExceededRateAmount = 'IncludedServicesAmountExceededRateAmount';
    case ServiceFullyBooked = 'ServiceFullyBooked';
    case RateDoesNotSatifyHurdles = 'RateDoesNotSatifyHurdles';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
