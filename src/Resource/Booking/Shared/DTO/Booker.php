<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Booker
{
    public function __construct(
        public string $lastName,
        public ?string $title = null,
        public ?string $gender = null,
        public ?string $firstName = null,
        public ?string $middleInitial = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?PersonAddress $address = null,
        public ?string $nationalityCountryCode = null,
        public ?string $identificationNumber = null,
        public ?string $identificationIssueDate = null,
        public ?string $identificationExpiryDate = null,
        public ?string $identificationType = null,
        public ?PersonCompany $company = null,
        public ?string $preferredLanguage = null,
        public ?string $birthDate = null,
        public ?string $birthPlace = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $address = ResponseData::nested($data, 'address');
        $company = ResponseData::nested($data, 'company');

        return new self(
            lastName: ResponseData::string($data, 'lastName'),
            title: ResponseData::nullableString($data, 'title'),
            gender: ResponseData::nullableString($data, 'gender'),
            firstName: ResponseData::nullableString($data, 'firstName'),
            middleInitial: ResponseData::nullableString($data, 'middleInitial'),
            email: ResponseData::nullableString($data, 'email'),
            phone: ResponseData::nullableString($data, 'phone'),
            address: $address !== [] ? PersonAddress::fromArray($address) : null,
            nationalityCountryCode: ResponseData::nullableString($data, 'nationalityCountryCode'),
            identificationNumber: ResponseData::nullableString($data, 'identificationNumber'),
            identificationIssueDate: ResponseData::nullableString($data, 'identificationIssueDate'),
            identificationExpiryDate: ResponseData::nullableString($data, 'identificationExpiryDate'),
            identificationType: ResponseData::nullableString($data, 'identificationType'),
            company: $company !== [] ? PersonCompany::fromArray($company) : null,
            preferredLanguage: ResponseData::nullableString($data, 'preferredLanguage'),
            birthDate: ResponseData::nullableString($data, 'birthDate'),
            birthPlace: ResponseData::nullableString($data, 'birthPlace'),
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
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address?->toArray(),
            'nationalityCountryCode' => $this->nationalityCountryCode,
            'identificationNumber' => $this->identificationNumber,
            'identificationIssueDate' => $this->identificationIssueDate,
            'identificationExpiryDate' => $this->identificationExpiryDate,
            'identificationType' => $this->identificationType,
            'company' => $this->company?->toArray(),
            'preferredLanguage' => $this->preferredLanguage,
            'birthDate' => $this->birthDate,
            'birthPlace' => $this->birthPlace,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
