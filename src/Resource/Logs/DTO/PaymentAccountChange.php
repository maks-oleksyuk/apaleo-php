<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PaymentAccountChange
{
    public function __construct(
        public ?string $accountNumber,
        public ?string $accountHolder,
        public ?string $expiryMonth,
        public ?string $expiryYear,
        public ?string $paymentMethod,
        public ?string $payerEmail,
        public ?bool $isVirtual,
        public ?bool $isActive,
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
            isVirtual: \array_key_exists('isVirtual', $data) ? ResponseData::bool($data, 'isVirtual') : null,
            isActive: \array_key_exists('isActive', $data) ? ResponseData::bool($data, 'isActive') : null,
        );
    }
}
