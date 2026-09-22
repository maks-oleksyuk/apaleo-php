<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Commission;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\ExternalReferences;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Guest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\TravelPurpose;

/** The payload for creating a reservation, whether standalone (within a new/existing booking) or as part of a group. */
final readonly class CreateReservation
{
    /**
     * @param list<CreateReservationTimeSlice> $timeSlices
     * @param list<int>                        $childrenAges
     * @param list<Guest>                      $additionalGuests
     * @param list<BookReservationService>     $services
     */
    public function __construct(
        public string $arrival,
        public string $departure,
        public int $adults,
        public ChannelCode $channelCode,
        public array $timeSlices,
        public array $childrenAges = [],
        public ?string $comment = null,
        public ?string $guestComment = null,
        public ?string $externalCode = null,
        public ?string $source = null,
        public ?Guest $primaryGuest = null,
        public array $additionalGuests = [],
        public ?GuaranteeType $guaranteeType = null,
        public ?TravelPurpose $travelPurpose = null,
        public array $services = [],
        public ?string $companyId = null,
        public ?string $corporateCode = null,
        public ?MonetaryValue $prePaymentAmount = null,
        public ?Commission $commission = null,
        public ?string $promoCode = null,
        public ?ExternalReferences $externalReferences = null,
        public ?RegisteredCard $registeredCard = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'arrival' => $this->arrival,
            'departure' => $this->departure,
            'adults' => $this->adults,
            'channelCode' => $this->channelCode->value,
            'childrenAges' => $this->childrenAges !== [] ? $this->childrenAges : null,
            'comment' => $this->comment,
            'guestComment' => $this->guestComment,
            'externalCode' => $this->externalCode,
            'source' => $this->source,
            'primaryGuest' => $this->primaryGuest?->toArray(),
            'additionalGuests' => $this->additionalGuests !== [] ? array_map(static fn (Guest $g): array => $g->toArray(), $this->additionalGuests) : null,
            'guaranteeType' => $this->guaranteeType?->value,
            'travelPurpose' => $this->travelPurpose?->value,
            'timeSlices' => array_map(static fn (CreateReservationTimeSlice $s): array => $s->toArray(), $this->timeSlices),
            'services' => $this->services !== [] ? array_map(static fn (BookReservationService $s): array => $s->toArray(), $this->services) : null,
            'companyId' => $this->companyId,
            'corporateCode' => $this->corporateCode,
            'prePaymentAmount' => $this->prePaymentAmount?->toArray(),
            'commission' => $this->commission?->toArray(),
            'promoCode' => $this->promoCode,
            'externalReferences' => $this->externalReferences?->toArray(),
            'registeredCard' => $this->registeredCard?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
