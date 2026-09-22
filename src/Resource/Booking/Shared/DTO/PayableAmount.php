<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PayableAmount
{
    public function __construct(
        public MonetaryValue $guest,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            guest: MonetaryValue::fromArray(ResponseData::nested($data, 'guest')),
        );
    }
}
