<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class BankAccount
{
    public function __construct(
        public ?string $iban,
        public ?string $bic,
        public ?string $bank,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            iban: ResponseData::nullableString($data, 'iban'),
            bic: ResponseData::nullableString($data, 'bic'),
            bank: ResponseData::nullableString($data, 'bank'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'iban' => $this->iban,
            'bic' => $this->bic,
            'bank' => $this->bank,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
