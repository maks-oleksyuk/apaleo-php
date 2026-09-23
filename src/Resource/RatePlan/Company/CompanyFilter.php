<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company;

/** Filter criteria for CompanyResource::list(). */
final readonly class CompanyFilter
{
    /**
     * @param list<string> $ratePlanIds
     * @param list<string> $corporateCodes
     */
    public function __construct(
        public ?string $propertyId = null,
        public array $ratePlanIds = [],
        public array $corporateCodes = [],
        public ?string $textSearch = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'ratePlanIds' => implode(',', $this->ratePlanIds) ?: null,
            'corporateCodes' => implode(',', $this->corporateCodes) ?: null,
            'textSearch' => $this->textSearch,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
