<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A charge collected on behalf of a third party (e.g. theater tickets): passed through, not revenue. */
final readonly class TransitoryCharge
{
    public function __construct(
        public string $id,
        public string $name,
        public MonetaryValue $amount,
        public ?FinanceServiceType $serviceType,
        public \DateTimeImmutable $serviceDate,
        public \DateTimeImmutable $created,
        public int $quantity,
        public ?string $receipt,
        public ?EmbeddedFolio $movedFrom,
        public ?EmbeddedFolio $movedTo,
        public ?string $movedReason,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $serviceType = ResponseData::nullableString($data, 'serviceType');

        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            serviceType: $serviceType !== null ? FinanceServiceType::fromApi($serviceType) : null,
            serviceDate: ResponseData::date($data, 'serviceDate'),
            created: ResponseData::dateTime($data, 'created'),
            quantity: ResponseData::int($data, 'quantity'),
            receipt: ResponseData::nullableString($data, 'receipt'),
            movedFrom: EmbeddedFolio::fromNested($data, 'movedFrom'),
            movedTo: EmbeddedFolio::fromNested($data, 'movedTo'),
            movedReason: ResponseData::nullableString($data, 'movedReason'),
        );
    }
}
