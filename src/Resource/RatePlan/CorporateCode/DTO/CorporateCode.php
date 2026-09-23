<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CorporateCode\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CorporateCode
{
    public function __construct(
        public string $code,
        public string $companyId,
        public string $companyCode,
        public string $companyName,
        public string $ratePlanId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ResponseData::string($data, 'code'),
            companyId: ResponseData::string($data, 'companyId'),
            companyCode: ResponseData::string($data, 'companyCode'),
            companyName: ResponseData::string($data, 'companyName'),
            ratePlanId: ResponseData::string($data, 'ratePlanId'),
        );
    }
}
