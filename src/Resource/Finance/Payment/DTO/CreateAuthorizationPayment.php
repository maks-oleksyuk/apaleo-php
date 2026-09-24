<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Enum\AuthorizationReferenceType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\PaidCharge;

/** Captures from an existing pre-authorization, identified by $transactionReference. */
final readonly class CreateAuthorizationPayment
{
    /** @param list<PaidCharge> $paidCharges */
    public function __construct(
        public string $transactionReference,
        public MonetaryValue $amount,
        public ?AuthorizationReferenceType $referenceType = null,
        public array $paidCharges = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'transactionReference' => $this->transactionReference,
            'amount' => $this->amount->toArray(),
            'referenceType' => $this->referenceType?->value,
            'paidCharges' => array_map(static fn (PaidCharge $c): array => $c->toArray(), $this->paidCharges) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
