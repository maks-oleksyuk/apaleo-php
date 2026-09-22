<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property;

use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;

/** Filter criteria for PropertyResource::list() (Apaleo's properties/$count takes none). */
final readonly class PropertyFilter
{
    /**
     * @param list<PropertyStatus> $status
     * @param list<string>         $countryCode ISO Alpha-2 country codes
     */
    public function __construct(
        public array $status = [],
        public ?bool $includeArchived = null,
        public array $countryCode = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'status' => implode(',', array_map(static fn (PropertyStatus $s): string => $s->value, $this->status)) ?: null,
            'includeArchived' => $this->includeArchived,
            'countryCode' => implode(',', $this->countryCode) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
