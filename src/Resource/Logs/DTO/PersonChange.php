<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\PersonAddress;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\PersonCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\VehicleRegistration;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * A guest/company/address diff as it appears in a reservation change log entry — every field is
 * optional, unlike Booking\Shared\DTO\Guest (a full guest record, which requires lastName).
 * title/gender/identificationType/relationshipToPrimaryGuest are kept as raw strings, mirroring
 * Guest's own convention for these country-dependent lookups.
 */
final readonly class PersonChange
{
    public function __construct(
        public ?string $title,
        public ?string $gender,
        public ?string $firstName,
        public ?string $middleInitial,
        public ?string $lastName,
        public ?string $secondLastName,
        public ?string $email,
        public ?string $phone,
        public ?PersonAddress $address,
        public ?string $nationalityCountryCode,
        public ?string $identificationNumber,
        public ?string $identificationAdditionalNumber,
        public ?string $identificationIssueDate,
        public ?string $identificationExpiryDate,
        public ?string $identificationIssuePlace,
        public ?string $identificationType,
        public ?string $personalTaxId,
        public ?PersonCompany $company,
        public ?string $preferredLanguage,
        public ?string $birthDate,
        public ?string $birthPlace,
        public ?string $birthFirstName,
        public ?string $birthLastName,
        public ?string $motherFirstName,
        public ?string $motherLastName,
        public ?string $borderCrossingPlace,
        public ?string $borderCrossingDate,
        public ?string $nextDestination,
        public ?string $relationshipToPrimaryGuest,
        public ?VehicleRegistration $vehicleRegistration,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $address = ResponseData::nested($data, 'address');
        $company = ResponseData::nested($data, 'company');
        $vehicleRegistration = ResponseData::nested($data, 'vehicleRegistration');

        return new self(
            title: ResponseData::nullableString($data, 'title'),
            gender: ResponseData::nullableString($data, 'gender'),
            firstName: ResponseData::nullableString($data, 'firstName'),
            middleInitial: ResponseData::nullableString($data, 'middleInitial'),
            lastName: ResponseData::nullableString($data, 'lastName'),
            secondLastName: ResponseData::nullableString($data, 'secondLastName'),
            email: ResponseData::nullableString($data, 'email'),
            phone: ResponseData::nullableString($data, 'phone'),
            address: [] !== $address ? PersonAddress::fromArray($address) : null,
            nationalityCountryCode: ResponseData::nullableString($data, 'nationalityCountryCode'),
            identificationNumber: ResponseData::nullableString($data, 'identificationNumber'),
            identificationAdditionalNumber: ResponseData::nullableString($data, 'identificationAdditionalNumber'),
            identificationIssueDate: ResponseData::nullableString($data, 'identificationIssueDate'),
            identificationExpiryDate: ResponseData::nullableString($data, 'identificationExpiryDate'),
            identificationIssuePlace: ResponseData::nullableString($data, 'identificationIssuePlace'),
            identificationType: ResponseData::nullableString($data, 'identificationType'),
            personalTaxId: ResponseData::nullableString($data, 'personalTaxId'),
            company: [] !== $company ? PersonCompany::fromArray($company) : null,
            preferredLanguage: ResponseData::nullableString($data, 'preferredLanguage'),
            birthDate: ResponseData::nullableString($data, 'birthDate'),
            birthPlace: ResponseData::nullableString($data, 'birthPlace'),
            birthFirstName: ResponseData::nullableString($data, 'birthFirstName'),
            birthLastName: ResponseData::nullableString($data, 'birthLastName'),
            motherFirstName: ResponseData::nullableString($data, 'motherFirstName'),
            motherLastName: ResponseData::nullableString($data, 'motherLastName'),
            borderCrossingPlace: ResponseData::nullableString($data, 'borderCrossingPlace'),
            borderCrossingDate: ResponseData::nullableString($data, 'borderCrossingDate'),
            nextDestination: ResponseData::nullableString($data, 'nextDestination'),
            relationshipToPrimaryGuest: ResponseData::nullableString($data, 'relationshipToPrimaryGuest'),
            vehicleRegistration: [] !== $vehicleRegistration ? VehicleRegistration::fromArray($vehicleRegistration) : null,
        );
    }
}
