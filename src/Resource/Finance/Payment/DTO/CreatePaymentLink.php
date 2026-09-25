<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\PaidCharge;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class CreatePaymentLink
{
    /** @param list<PaidCharge> $paidCharges */
    public function __construct(
        public \DateTimeImmutable $expiresAt,
        public string $countryCode,
        public MonetaryValue $amount,
        public ?string $description = null,
        public ?string $payerEmail = null,
        public ?string $returnUrl = null,
        public array $paidCharges = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'expiresAt' => $this->expiresAt->format(\DateTimeInterface::ATOM),
            'countryCode' => $this->countryCode,
            'amount' => $this->amount->toArray(),
            'description' => $this->description,
            'payerEmail' => $this->payerEmail,
            'returnUrl' => $this->returnUrl,
            'paidCharges' => array_map(static fn (PaidCharge $c): array => $c->toArray(), $this->paidCharges) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
