<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Enum\CancellationPolicyReference;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\FeeDetails;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\Period;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CancellationPolicyListItem
{
    /**
     * @param Period $periodFromReference how long before arrival / after booking the guest can still cancel for free
     */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public string $name,
        public string $description,
        public Period $periodFromReference,
        public CancellationPolicyReference $reference,
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
            periodFromReference: Period::fromArray(ResponseData::nested($data, 'periodFromReference')),
            reference: CancellationPolicyReference::fromApi(ResponseData::string($data, 'reference')),
            fee: FeeDetails::fromArray(ResponseData::nested($data, 'fee')),
        );
    }
}
