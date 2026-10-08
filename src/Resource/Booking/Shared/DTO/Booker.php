<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\IdentificationType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\Gender;
use Oleksyuk\Apaleo\Resource\Shared\Enum\Title;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Booker
{
    public function __construct(
        public string $lastName,
        public ?Title $title = null,
        public ?Gender $gender = null,
        public ?string $firstName = null,
        public ?string $middleInitial = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?PersonAddress $address = null,
        public ?string $nationalityCountryCode = null,
        public ?string $identificationNumber = null,
        public ?\DateTimeImmutable $identificationIssueDate = null,
        public ?\DateTimeImmutable $identificationExpiryDate = null,
        public ?IdentificationType $identificationType = null,
        public ?PersonCompany $company = null,
        public ?string $preferredLanguage = null,
        public ?\DateTimeImmutable $birthDate = null,
        public ?string $birthPlace = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            lastName: ResponseData::string($data, 'lastName'),
            title: ResponseData::nullableEnum($data, 'title', Title::fromApi(...)),
            gender: ResponseData::nullableEnum($data, 'gender', Gender::fromApi(...)),
            firstName: ResponseData::nullableString($data, 'firstName'),
            middleInitial: ResponseData::nullableString($data, 'middleInitial'),
            email: ResponseData::nullableString($data, 'email'),
            phone: ResponseData::nullableString($data, 'phone'),
            address: ResponseData::nullableNested($data, 'address', PersonAddress::fromArray(...)),
            nationalityCountryCode: ResponseData::nullableString($data, 'nationalityCountryCode'),
            identificationNumber: ResponseData::nullableString($data, 'identificationNumber'),
            identificationIssueDate: ResponseData::nullableDate($data, 'identificationIssueDate'),
            identificationExpiryDate: ResponseData::nullableDate($data, 'identificationExpiryDate'),
            identificationType: ResponseData::nullableEnum($data, 'identificationType', IdentificationType::fromApi(...)),
            company: ResponseData::nullableNested($data, 'company', PersonCompany::fromArray(...)),
            preferredLanguage: ResponseData::nullableString($data, 'preferredLanguage'),
            birthDate: ResponseData::nullableDate($data, 'birthDate'),
            birthPlace: ResponseData::nullableString($data, 'birthPlace'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'lastName' => $this->lastName,
            'title' => $this->title?->value,
            'gender' => $this->gender?->value,
            'firstName' => $this->firstName,
            'middleInitial' => $this->middleInitial,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address?->toArray(),
            'nationalityCountryCode' => $this->nationalityCountryCode,
            'identificationNumber' => $this->identificationNumber,
            'identificationIssueDate' => $this->identificationIssueDate?->format('Y-m-d'),
            'identificationExpiryDate' => $this->identificationExpiryDate?->format('Y-m-d'),
            'identificationType' => $this->identificationType?->value,
            'company' => $this->company?->toArray(),
            'preferredLanguage' => $this->preferredLanguage,
            'birthDate' => $this->birthDate?->format('Y-m-d'),
            'birthPlace' => $this->birthPlace,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
