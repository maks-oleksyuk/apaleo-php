<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RatePlanAgeCategory
{
    /** @param list<AgeCategorySurcharge> $surcharges */
    public function __construct(
        public string $id,
        public array $surcharges,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            surcharges: array_map(AgeCategorySurcharge::fromArray(...), ResponseData::nestedList($data, 'surcharges')),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'surcharges' => array_map(static fn (AgeCategorySurcharge $s): array => $s->toArray(), $this->surcharges),
        ];
    }
}
