<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\PromoCode\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PromoCode
{
    /** @param list<string> $relatedRatePlanIds */
    public function __construct(
        public string $code,
        public array $relatedRatePlanIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ResponseData::string($data, 'code'),
            relatedRatePlanIds: ResponseData::stringListOrEmpty($data, 'relatedRateplanIds'),
        );
    }
}
