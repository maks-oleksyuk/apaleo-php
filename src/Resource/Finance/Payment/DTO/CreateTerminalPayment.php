<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\PaidCharge;

final readonly class CreateTerminalPayment
{
    /** @param list<PaidCharge> $paidCharges */
    public function __construct(
        public string $terminalId,
        public MonetaryValue $amount,
        public array $paidCharges = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'terminalId' => $this->terminalId,
            'amount' => $this->amount->toArray(),
            'paidCharges' => array_map(static fn (PaidCharge $c): array => $c->toArray(), $this->paidCharges) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
