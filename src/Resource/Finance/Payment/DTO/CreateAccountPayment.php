<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Payment\Enum\PaymentAccountOwner;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\PaidCharge;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

/** Charges a stored payment account: pick it by $paymentAccountId, or by $accountOwner to use the guest's or booker's. */
final readonly class CreateAccountPayment
{
    /** @param list<PaidCharge> $paidCharges */
    public function __construct(
        public MonetaryValue $amount,
        public ?PaymentAccountOwner $accountOwner = null,
        public ?string $paymentAccountId = null,
        public array $paidCharges = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount->toArray(),
            'accountOwner' => $this->accountOwner?->value,
            'paymentAccountId' => $this->paymentAccountId,
            'paidCharges' => array_map(static fn (PaidCharge $c): array => $c->toArray(), $this->paidCharges) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
