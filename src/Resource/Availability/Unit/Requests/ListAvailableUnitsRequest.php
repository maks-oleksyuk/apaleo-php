<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;

final readonly class ListAvailableUnitsRequest extends Request
{
    /** @param list<string> $unitAttributeIds */
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private ?string $unitGroupId = null,
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
        return '/availability/v1/units';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'unitGroupId' => $this->unitGroupId,
            'from' => $this->from->format(\DateTimeInterface::ATOM),
            'to' => $this->to->format(\DateTimeInterface::ATOM),
            'includeOutOfService' => $this->includeOutOfService,
            'unitCondition' => $this->unitCondition?->value,
            'unitAttributeIds' => implode(',', $this->unitAttributeIds) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
