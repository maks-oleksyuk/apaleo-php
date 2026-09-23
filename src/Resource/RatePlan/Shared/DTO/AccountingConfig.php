<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AccountingConfig
{
    public function __construct(
        public ServiceType $serviceType,
        public VatType $vatType,
        public \DateTimeImmutable $validFrom,
        public ?string $subAccountId = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            serviceType: ServiceType::fromApi(ResponseData::string($data, 'serviceType')),
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            validFrom: ResponseData::date($data, 'validFrom'),
            subAccountId: ResponseData::nullableString($data, 'subAccountId'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'serviceType' => $this->serviceType->value,
            'vatType' => $this->vatType->value,
            'validFrom' => $this->validFrom->format('Y-m-d'),
            'subAccountId' => $this->subAccountId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
