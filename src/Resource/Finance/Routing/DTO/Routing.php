<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\DebitorType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Routing
{
    /** @param list<Action> $actions only filled with expand: ['actions'] */
    public function __construct(
        public string $id,
        public string $bookingId,
        public string $propertyId,
        public string $destinationFolioId,
        public ?DebitorType $destinationDebitorType,
        public ?RoutingChargeFilter $filter,
        public array $actions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $destination = ResponseData::nested($data, 'destinationFolio');
        $debitorType = ResponseData::nullableString($destination, 'debitorType');
        $filter = ResponseData::nested($data, 'filter');

        return new self(
            id: ResponseData::string($data, 'id'),
            bookingId: ResponseData::string($data, 'bookingId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            destinationFolioId: ResponseData::string($destination, 'id'),
            destinationDebitorType: $debitorType !== null ? DebitorType::fromApi($debitorType) : null,
            filter: $filter !== [] ? RoutingChargeFilter::fromArray($filter) : null,
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
        );
    }
}
