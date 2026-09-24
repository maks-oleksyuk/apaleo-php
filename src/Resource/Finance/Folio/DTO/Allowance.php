<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\EmbeddedFolio;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A reduction granted on a folio; $sourceChargeId is set when it was granted on one specific charge. */
final readonly class Allowance
{
    public function __construct(
        public string $id,
        public Amount $amount,
        public string $reason,
        public FinanceServiceType $serviceType,
        public \DateTimeImmutable $serviceDate,
        public \DateTimeImmutable $created,
        public ?string $sourceChargeId,
        public ?string $subAccountId,
        public ?EmbeddedFolio $movedFrom,
        public ?EmbeddedFolio $movedTo,
        public ?string $movedReason,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            reason: ResponseData::string($data, 'reason'),
            serviceType: FinanceServiceType::fromApi(ResponseData::string($data, 'serviceType')),
            serviceDate: ResponseData::date($data, 'serviceDate'),
            created: ResponseData::dateTime($data, 'created'),
            sourceChargeId: ResponseData::nullableString($data, 'sourceChargeId'),
            subAccountId: ResponseData::nullableString($data, 'subAccountId'),
            movedFrom: EmbeddedFolio::fromNested($data, 'movedFrom'),
            movedTo: EmbeddedFolio::fromNested($data, 'movedTo'),
            movedReason: ResponseData::nullableString($data, 'movedReason'),
        );
    }
}
