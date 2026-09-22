<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\UnitCondition;

/** Alternative units a reservation could be moved to. */
final readonly class ListReservationAvailableUnitsRequest extends Request
{
    /** @param list<string> $unitAttributeIds */
    public function __construct(
        private string $reservationId,
        private ?string $unitGroupId = null,
        private ?\DateTimeImmutable $from = null,
        private ?\DateTimeImmutable $to = null,
        private ?bool $includeOutOfService = null,
        private ?UnitCondition $unitCondition = null,
        private array $unitAttributeIds = [],
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/availability/v1/reservations/'.rawurlencode($this->reservationId).'/units';
    }

    public function query(): array
    {
        return array_filter([
            'unitGroupId' => $this->unitGroupId,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'includeOutOfService' => $this->includeOutOfService,
            'unitCondition' => $this->unitCondition?->value,
            'unitAttributeIds' => implode(',', $this->unitAttributeIds) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
