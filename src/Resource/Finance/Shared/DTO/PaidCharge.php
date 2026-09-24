<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\DTO;

/** Assigns part of a payment to one charge, e.g. to mark which charges a partial payment covers. */
final readonly class PaidCharge
{
    public function __construct(
        public string $chargeId,
        public float $amount,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['chargeId' => $this->chargeId, 'amount' => $this->amount];
    }
}
