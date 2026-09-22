<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OfferCancellationFee
{
    public function __construct(
        public string $code,
        public string $name,
        public string $description,
        public \DateTimeImmutable $dueDateTime,
        public MonetaryValue $fee,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            dueDateTime: ResponseData::dateTime($data, 'dueDateTime'),
            fee: MonetaryValue::fromArray(ResponseData::nested($data, 'fee')),
        );
    }
}
