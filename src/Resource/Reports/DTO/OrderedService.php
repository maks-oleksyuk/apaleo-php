<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OrderedService
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public \DateTimeImmutable $serviceDate,
        public int $count,
        public ?OrderedServiceGuest $guest,
        public OrderedServiceReservation $reservation,
        public ?OrderedServiceUnit $unit,
        public OrderedServiceUnitGroup $unitGroup,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $guest = ResponseData::nested($data, 'guest');
        $unit = ResponseData::nested($data, 'unit');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            serviceDate: ResponseData::dateTime($data, 'serviceDate'),
            count: ResponseData::int($data, 'count'),
            guest: [] !== $guest ? OrderedServiceGuest::fromArray($guest) : null,
            reservation: OrderedServiceReservation::fromArray(ResponseData::nested($data, 'reservation')),
            unit: [] !== $unit ? OrderedServiceUnit::fromArray($unit) : null,
            unitGroup: OrderedServiceUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
        );
    }
}
