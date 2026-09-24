<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\PersonAddress;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\DebitorType;
use Oleksyuk\Apaleo\Resource\Reports\Enum\Title;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Who a folio is billed to: a person (firstName/name) or a company. */
final readonly class FolioDebitor
{
    public function __construct(
        public ?DebitorType $type = null,
        public ?Title $title = null,
        public ?string $firstName = null,
        public ?string $name = null,
        public ?PersonAddress $address = null,
        public ?CompanyInfo $company = null,
        public ?string $personalTaxId = null,
        public ?string $reference = null,
        public ?string $email = null,
        public ?string $phone = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::nullableString($data, 'type');
        $title = ResponseData::nullableString($data, 'title');
        $address = ResponseData::nested($data, 'address');
        $company = ResponseData::nested($data, 'company');

        return new self(
            type: $type !== null ? DebitorType::fromApi($type) : null,
            title: $title !== null ? Title::fromApi($title) : null,
            firstName: ResponseData::nullableString($data, 'firstName'),
            name: ResponseData::nullableString($data, 'name'),
            address: $address !== [] ? PersonAddress::fromArray($address) : null,
            company: $company !== [] ? CompanyInfo::fromArray($company) : null,
            personalTaxId: ResponseData::nullableString($data, 'personalTaxId'),
            reference: ResponseData::nullableString($data, 'reference'),
            email: ResponseData::nullableString($data, 'email'),
            phone: ResponseData::nullableString($data, 'phone'),
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromNested(array $data, string $key): ?self
    {
        $debitor = ResponseData::nested($data, $key);

        return $debitor !== [] ? self::fromArray($debitor) : null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type?->value,
            'title' => $this->title?->value,
            'firstName' => $this->firstName,
            'name' => $this->name,
            'address' => $this->address?->toArray(),
            'company' => $this->company?->toArray(),
            'personalTaxId' => $this->personalTaxId,
            'reference' => $this->reference,
            'email' => $this->email,
            'phone' => $this->phone,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
