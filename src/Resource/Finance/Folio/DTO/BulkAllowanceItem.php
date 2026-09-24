<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

final readonly class BulkAllowanceItem
{
    public function __construct(
        public string $chargeId,
        public MonetaryValue $amount,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['chargeId' => $this->chargeId, 'amount' => $this->amount->toArray()];
    }
}
