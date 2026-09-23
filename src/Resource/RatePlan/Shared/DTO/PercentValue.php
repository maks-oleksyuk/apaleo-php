<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PercentValue
{
    /**
     * @param ?int $limit max number of nights the percentage is charged for
     * @param list<string> $includeServiceIds services whose price is included in the percentage base
     */
    public function __construct(
        public int $percent,
        public ?int $limit = null,
        public array $includeServiceIds = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            percent: ResponseData::int($data, 'percent'),
            limit: ResponseData::nullableInt($data, 'limit'),
            includeServiceIds: ResponseData::stringListOrEmpty($data, 'includeServiceIds'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'percent' => $this->percent,
            'limit' => $this->limit,
            'includeServiceIds' => $this->includeServiceIds ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
