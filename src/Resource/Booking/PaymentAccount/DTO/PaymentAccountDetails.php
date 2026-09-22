<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PaymentAccountDetails
{
    public function __construct(
        public ?string $accountNumber,
        public ?string $accountHolder,
        public ?string $expiryMonth,
        public ?string $expiryYear,
        public ?string $paymentMethod,
        public ?string $payerEmail,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: ResponseData::nullableString($data, 'accountNumber'),
            accountHolder: ResponseData::nullableString($data, 'accountHolder'),
            expiryMonth: ResponseData::nullableString($data, 'expiryMonth'),
            expiryYear: ResponseData::nullableString($data, 'expiryYear'),
            paymentMethod: ResponseData::nullableString($data, 'paymentMethod'),
            payerEmail: ResponseData::nullableString($data, 'payerEmail'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'accountNumber' => $this->accountNumber,
            'accountHolder' => $this->accountHolder,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'paymentMethod' => $this->paymentMethod,
            'payerEmail' => $this->payerEmail,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
