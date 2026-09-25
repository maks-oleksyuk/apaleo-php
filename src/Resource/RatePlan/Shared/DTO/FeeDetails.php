<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A cancellation or no-show fee: either a fixed amount or a percentage of the stay. */
final readonly class FeeDetails
{
    public function __construct(
        public VatType $vatType,
        public ?MonetaryValue $fixedValue = null,
        public ?PercentValue $percentValue = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $fixedValue = ResponseData::nested($data, 'fixedValue');
        $percentValue = ResponseData::nested($data, 'percentValue');

        return new self(
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            fixedValue: $fixedValue !== [] ? MonetaryValue::fromArray($fixedValue) : null,
            percentValue: $percentValue !== [] ? PercentValue::fromArray($percentValue) : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'vatType' => $this->vatType->value,
            'fixedValue' => $this->fixedValue?->toArray(),
            'percentValue' => $this->percentValue?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
