<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RegisteredCard
{
    public function __construct(
        public ?string $cardNumber,
        public ?string $cardHolder,
        public ?string $expiryMonth,
        public ?string $expiryYear,
        public ?string $paymentMethod,
        public ?string $payerEmail,
        public ?string $note,
        public bool $isVirtual,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            cardNumber: ResponseData::nullableString($data, 'cardNumber'),
            cardHolder: ResponseData::nullableString($data, 'cardHolder'),
            expiryMonth: ResponseData::nullableString($data, 'expiryMonth'),
            expiryYear: ResponseData::nullableString($data, 'expiryYear'),
            paymentMethod: ResponseData::nullableString($data, 'paymentMethod'),
            payerEmail: ResponseData::nullableString($data, 'payerEmail'),
            note: ResponseData::nullableString($data, 'note'),
            isVirtual: ResponseData::bool($data, 'isVirtual'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'cardNumber' => $this->cardNumber,
            'cardHolder' => $this->cardHolder,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'paymentMethod' => $this->paymentMethod,
            'payerEmail' => $this->payerEmail,
            'note' => $this->note,
            'isVirtual' => $this->isVirtual,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
