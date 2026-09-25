<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * A reservation's primary or additional guest, used both for reading (fromArray) and creating
 * (toArray). Fields whose allowed values apaleo publishes as a lookup (title, gender,
 * identificationType, relationshipToPrimaryGuest) are kept as raw strings rather than enums:
 * gender/identificationType are already country-dependent (see TypesResource::allowedValues()).
 */
final readonly class Guest
{
    public function __construct(
        public string $lastName,
        public ?string $title = null,
        public ?string $gender = null,
        public ?string $firstName = null,
        public ?string $middleInitial = null,
        public ?string $secondLastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?PersonAddress $address = null,
        public ?string $nationalityCountryCode = null,
        public ?string $identificationNumber = null,
        public ?string $identificationAdditionalNumber = null,
        public ?string $identificationIssueDate = null,
        public ?string $identificationExpiryDate = null,
        public ?string $identificationIssuePlace = null,
        public ?string $identificationType = null,
        public ?string $personalTaxId = null,
        public ?PersonCompany $company = null,
        public ?string $preferredLanguage = null,
        public ?string $birthDate = null,
        public ?string $birthPlace = null,
        public ?string $birthFirstName = null,
        public ?string $birthLastName = null,
        public ?string $motherFirstName = null,
        public ?string $motherLastName = null,
        public ?string $borderCrossingPlace = null,
        public ?string $borderCrossingDate = null,
        public ?string $nextDestination = null,
        public ?string $relationshipToPrimaryGuest = null,
        public ?VehicleRegistration $vehicleRegistration = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            lastName: ResponseData::string($data, 'lastName'),
            title: ResponseData::nullableString($data, 'title'),
            gender: ResponseData::nullableString($data, 'gender'),
            firstName: ResponseData::nullableString($data, 'firstName'),
            middleInitial: ResponseData::nullableString($data, 'middleInitial'),
            secondLastName: ResponseData::nullableString($data, 'secondLastName'),
            email: ResponseData::nullableString($data, 'email'),
            phone: ResponseData::nullableString($data, 'phone'),
            address: ResponseData::nullableNested($data, 'address', PersonAddress::fromArray(...)),
            nationalityCountryCode: ResponseData::nullableString($data, 'nationalityCountryCode'),
            identificationNumber: ResponseData::nullableString($data, 'identificationNumber'),
            identificationAdditionalNumber: ResponseData::nullableString($data, 'identificationAdditionalNumber'),
            identificationIssueDate: ResponseData::nullableString($data, 'identificationIssueDate'),
            identificationExpiryDate: ResponseData::nullableString($data, 'identificationExpiryDate'),
            identificationIssuePlace: ResponseData::nullableString($data, 'identificationIssuePlace'),
            identificationType: ResponseData::nullableString($data, 'identificationType'),
            personalTaxId: ResponseData::nullableString($data, 'personalTaxId'),
            company: ResponseData::nullableNested($data, 'company', PersonCompany::fromArray(...)),
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
            vehicleRegistration: ResponseData::nullableNested($data, 'vehicleRegistration', VehicleRegistration::fromArray(...)),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'lastName' => $this->lastName,
            'title' => $this->title,
            'gender' => $this->gender,
            'firstName' => $this->firstName,
            'middleInitial' => $this->middleInitial,
            'secondLastName' => $this->secondLastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address?->toArray(),
            'nationalityCountryCode' => $this->nationalityCountryCode,
            'identificationNumber' => $this->identificationNumber,
            'identificationAdditionalNumber' => $this->identificationAdditionalNumber,
            'identificationIssueDate' => $this->identificationIssueDate,
            'identificationExpiryDate' => $this->identificationExpiryDate,
            'identificationIssuePlace' => $this->identificationIssuePlace,
            'identificationType' => $this->identificationType,
            'personalTaxId' => $this->personalTaxId,
            'company' => $this->company?->toArray(),
            'preferredLanguage' => $this->preferredLanguage,
            'birthDate' => $this->birthDate,
            'birthPlace' => $this->birthPlace,
            'birthFirstName' => $this->birthFirstName,
            'birthLastName' => $this->birthLastName,
            'motherFirstName' => $this->motherFirstName,
            'motherLastName' => $this->motherLastName,
            'borderCrossingPlace' => $this->borderCrossingPlace,
            'borderCrossingDate' => $this->borderCrossingDate,
            'nextDestination' => $this->nextDestination,
            'relationshipToPrimaryGuest' => $this->relationshipToPrimaryGuest,
            'vehicleRegistration' => $this->vehicleRegistration?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
