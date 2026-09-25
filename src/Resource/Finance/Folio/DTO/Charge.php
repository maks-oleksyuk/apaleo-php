<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\ChargeType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * $movedFrom/$movedTo track a manual move between folios, $routedFrom/$routedTo one done by a routing rule.
 */
final readonly class Charge
{
    /** @param array<string, string> $translatedNames */
    public function __construct(
        public string $id,
        public ChargeType $type,
        public FinanceServiceType $serviceType,
        public string $name,
        public array $translatedNames,
        public bool $isPosted,
        public \DateTimeImmutable $serviceDate,
        public \DateTimeImmutable $created,
        public Amount $amount,
        public int $quantity,
        public ?string $receipt,
        public ?string $groupId,
        public ?string $subAccountId,
        public ?EmbeddedFolio $movedFrom,
        public ?EmbeddedFolio $movedTo,
        public ?string $movedReason,
        public ?EmbeddedFolio $routedFrom,
        public ?EmbeddedFolio $routedTo,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            type: ChargeType::fromApi(ResponseData::string($data, 'type')),
            serviceType: FinanceServiceType::fromApi(ResponseData::string($data, 'serviceType')),
            name: ResponseData::string($data, 'name'),
            translatedNames: ResponseData::localizedText($data, 'translatedNames'),
            isPosted: ResponseData::bool($data, 'isPosted'),
            serviceDate: ResponseData::date($data, 'serviceDate'),
            created: ResponseData::dateTime($data, 'created'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            quantity: ResponseData::int($data, 'quantity'),
            receipt: ResponseData::nullableString($data, 'receipt'),
            groupId: ResponseData::nullableString($data, 'groupId'),
            subAccountId: ResponseData::nullableString($data, 'subAccountId'),
            movedFrom: ResponseData::nullableNested($data, 'movedFrom', EmbeddedFolio::fromArray(...)),
            movedTo: ResponseData::nullableNested($data, 'movedTo', EmbeddedFolio::fromArray(...)),
            movedReason: ResponseData::nullableString($data, 'movedReason'),
            routedFrom: ResponseData::nullableNested($data, 'routedFrom', EmbeddedFolio::fromArray(...)),
            routedTo: ResponseData::nullableNested($data, 'routedTo', EmbeddedFolio::fromArray(...)),
        );
    }
}
