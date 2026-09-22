<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A fee added on top of the offer's/service offer's total when booking (e.g. a resort fee). */
final readonly class OfferFee
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public Amount $totalAmount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            totalAmount: Amount::fromArray(ResponseData::nested($data, 'totalAmount')),
        );
    }
}
