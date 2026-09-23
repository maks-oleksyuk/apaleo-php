<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Enum\CancellationPolicyReference;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\FeeDetails;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\Period;

final readonly class CreateCancellationPolicy
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description localized
     */
    public function __construct(
        public string $code,
        public string $propertyId,
        public array $name,
        public array $description,
        public CancellationPolicyReference $reference,
        public FeeDetails $fee,
        public ?Period $periodFromReference = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'description' => $this->description,
            'periodFromReference' => $this->periodFromReference?->toArray(),
            'reference' => $this->reference->value,
            'fee' => $this->fee->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
