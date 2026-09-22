<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceChange
{
    public function __construct(
        public string $serviceId,
        public ?int $count,
        public ?Amount $amount,
        public ?Amount $totalAmount,
        public \DateTimeImmutable $serviceDate,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $amount = ResponseData::nested($data, 'amount');
        $totalAmount = ResponseData::nested($data, 'totalAmount');

        return new self(
            serviceId: ResponseData::string($data, 'serviceId'),
            count: ResponseData::nullableInt($data, 'count'),
            amount: [] !== $amount ? Amount::fromArray($amount) : null,
            totalAmount: [] !== $totalAmount ? Amount::fromArray($totalAmount) : null,
            serviceDate: ResponseData::date($data, 'serviceDate'),
        );
    }
}
