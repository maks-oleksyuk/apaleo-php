<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ArrivalsReport
{
    /**
     * @param list<TravelPurposeEntry> $travelPurposeBreakdown
     * @param list<CountryEntry>       $nationalityBreakdown
     * @param list<CountryEntry>       $countryOfResidenceBreakdown
     */
    public function __construct(
        public int $total,
        public int $totalAdults,
        public int $totalChildren,
        public array $travelPurposeBreakdown,
        public array $nationalityBreakdown,
        public array $countryOfResidenceBreakdown,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            total: ResponseData::int($data, 'total'),
            totalAdults: ResponseData::int($data, 'totalAdults'),
            totalChildren: ResponseData::int($data, 'totalChildren'),
            travelPurposeBreakdown: array_map(TravelPurposeEntry::fromArray(...), ResponseData::nestedList($data, 'travelPurposeBreakdown')),
            nationalityBreakdown: array_map(CountryEntry::fromArray(...), ResponseData::nestedList($data, 'nationalityBreakdown')),
            countryOfResidenceBreakdown: array_map(CountryEntry::fromArray(...), ResponseData::nestedList($data, 'countryOfResidenceBreakdown')),
        );
    }
}
