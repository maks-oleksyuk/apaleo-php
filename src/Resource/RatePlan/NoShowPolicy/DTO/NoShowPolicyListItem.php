<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\FeeDetails;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Item shape of GET /rateplan/v1/no-show-policies: unlike NoShowPolicy, $name and $description are plain strings, not localized maps. */
final readonly class NoShowPolicyListItem
{
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public string $name,
        public string $description,
        public FeeDetails $fee,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            fee: FeeDetails::fromArray(ResponseData::nested($data, 'fee')),
        );
    }
}
