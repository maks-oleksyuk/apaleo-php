<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Commission
{
    public function __construct(
        public MonetaryValue $commissionAmount,
        public ?MonetaryValue $beforeCommissionAmount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $before = ResponseData::nested($data, 'beforeCommissionAmount');

        return new self(
            commissionAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'commissionAmount')),
            beforeCommissionAmount: $before !== [] ? MonetaryValue::fromArray($before) : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'commissionAmount' => $this->commissionAmount->toArray(),
            'beforeCommissionAmount' => $this->beforeCommissionAmount?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
