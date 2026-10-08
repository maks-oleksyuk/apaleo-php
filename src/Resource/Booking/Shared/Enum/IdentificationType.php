<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\Enum;

/** The set apaleo accepts for a country is a subset of these (see TypesResource::allowedValues()). */
enum IdentificationType: string
{
    case SocialInsuranceNumber = 'SocialInsuranceNumber';
    case PassportNumber = 'PassportNumber';
    case IdNumber = 'IdNumber';
    case DriverLicenseNumber = 'DriverLicenseNumber';
    case VisaNumber = 'VisaNumber';
    case ForeignerIdentityNumber = 'ForeignerIdentityNumber';
    case TaxIdentificationNumber = 'TaxIdentificationNumber';
    case Other = 'Other';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
