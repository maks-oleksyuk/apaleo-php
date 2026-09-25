<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PendingPayment
{
    public function __construct(
        public string $id,
        public MonetaryValue $amount,
        public ?string $terminalId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            amount: MonetaryValue::fromArray(ResponseData::nested($data, 'amount')),
            terminalId: ResponseData::nullableString($data, 'terminalId'),
        );
    }
}
