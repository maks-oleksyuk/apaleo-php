<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\FeeDetails;

final readonly class CreateNoShowPolicy
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
        public FeeDetails $fee,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'description' => $this->description,
            'fee' => $this->fee->toArray(),
        ];
    }
}
