<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property;

use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Support\Query;

/** list() only: Apaleo's properties/$count takes no filter. */
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
            'status' => Query::csv($this->status),
            'includeArchived' => $this->includeArchived,
            'countryCode' => Query::csv($this->countryCode),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
