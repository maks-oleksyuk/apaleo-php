<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Property
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description
     * @param array<string, string> $paymentTerms
     */
    public function __construct(
        public string $id,
        public string $code,
        public ?string $propertyTemplateId,
        public bool $isTemplate,
        public array $name,
        public array $description,
        public string $companyName,
        public ?string $managingDirectors,
        public string $commercialRegisterEntry,
        public string $taxId,
        public Address $location,
        public ?BankAccount $bankAccount,
        public array $paymentTerms,
        public string $timeZone,
        public string $currencyCode,
        public PropertyStatus $status,
        public string $rawStatus,
        public bool $isArchived,
        public \DateTimeImmutable $created,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = ResponseData::string($data, 'status');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyTemplateId: ResponseData::nullableString($data, 'propertyTemplateId'),
            isTemplate: ResponseData::bool($data, 'isTemplate'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            companyName: ResponseData::string($data, 'companyName'),
            managingDirectors: ResponseData::nullableString($data, 'managingDirectors'),
            commercialRegisterEntry: ResponseData::string($data, 'commercialRegisterEntry'),
            taxId: ResponseData::string($data, 'taxId'),
            location: Address::fromArray(ResponseData::nested($data, 'location')),
            bankAccount: ResponseData::nullableNested($data, 'bankAccount', BankAccount::fromArray(...)),
            paymentTerms: ResponseData::localizedText($data, 'paymentTerms'),
            timeZone: ResponseData::string($data, 'timeZone'),
            currencyCode: ResponseData::string($data, 'currencyCode'),
            status: PropertyStatus::fromApi($status),
            rawStatus: $status,
            isArchived: ResponseData::bool($data, 'isArchived'),
            created: ResponseData::dateTime($data, 'created'),
        );
    }
}
