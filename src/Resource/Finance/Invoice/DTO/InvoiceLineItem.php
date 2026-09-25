<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoiceLineItem
{
    /** @param list<IncludedLineItem> $includedLineItems */
    public function __construct(
        public \DateTimeImmutable $date,
        public string $description,
        public MonetaryValue $price,
        public ?VatType $vatType,
        public ?float $vatPercent,
        public bool $isNoShowFee,
        public ?int $quantity,
        public ?string $guest,
        public array $includedLineItems,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $vatType = ResponseData::nullableString($data, 'vatType');

        return new self(
            date: ResponseData::date($data, 'date'),
            description: ResponseData::string($data, 'description'),
            price: MonetaryValue::fromArray(ResponseData::nested($data, 'price')),
            vatType: $vatType !== null ? VatType::fromApi($vatType) : null,
            vatPercent: ResponseData::nullableFloat($data, 'vatPercent'),
            isNoShowFee: ResponseData::bool($data, 'isNoShowFee'),
            quantity: ResponseData::nullableInt($data, 'quantity'),
            guest: ResponseData::nullableString($data, 'guest'),
            includedLineItems: array_map(IncludedLineItem::fromArray(...), ResponseData::nestedList($data, 'includedLineItems')),
        );
    }
}
