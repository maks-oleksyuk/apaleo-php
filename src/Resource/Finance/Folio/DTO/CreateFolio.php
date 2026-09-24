<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;

/**
 * A Guest folio needs $reservationId; an External folio needs $propertyId instead.
 */
final readonly class CreateFolio
{
    public function __construct(
        public FolioDebitor $debitor,
        public ?FolioType $type = null,
        public ?string $reservationId = null,
        public ?string $propertyId = null,
        public ?string $companyId = null,
        public ?string $code = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'debitor' => $this->debitor->toArray(),
            'type' => $this->type?->value,
            'reservationId' => $this->reservationId,
            'propertyId' => $this->propertyId,
            'companyId' => $this->companyId,
            'code' => $this->code,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
