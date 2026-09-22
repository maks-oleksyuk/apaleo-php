<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ExternalReferences
{
    public function __construct(
        public ?string $globalDistributionSystemId = null,
        public ?string $onlineTravelAgencyId = null,
        public ?string $onlineBookingToolId = null,
        public ?string $channelManagerId = null,
        public ?string $centralReservationSystemId = null,
        public ?string $legacyId = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            globalDistributionSystemId: ResponseData::nullableString($data, 'globalDistributionSystemId'),
            onlineTravelAgencyId: ResponseData::nullableString($data, 'onlineTravelAgencyId'),
            onlineBookingToolId: ResponseData::nullableString($data, 'onlineBookingToolId'),
            channelManagerId: ResponseData::nullableString($data, 'channelManagerId'),
            centralReservationSystemId: ResponseData::nullableString($data, 'centralReservationSystemId'),
            legacyId: ResponseData::nullableString($data, 'legacyId'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'globalDistributionSystemId' => $this->globalDistributionSystemId,
            'onlineTravelAgencyId' => $this->onlineTravelAgencyId,
            'onlineBookingToolId' => $this->onlineBookingToolId,
            'channelManagerId' => $this->channelManagerId,
            'centralReservationSystemId' => $this->centralReservationSystemId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
