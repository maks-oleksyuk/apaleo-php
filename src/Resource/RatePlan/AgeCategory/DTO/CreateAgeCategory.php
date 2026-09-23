<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO;

final readonly class CreateAgeCategory
{
    /** @param array<string, string> $name localized */
    public function __construct(
        public string $code,
        public string $propertyId,
        public array $name,
        public int $minAge,
        public int $maxAge,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'minAge' => $this->minAge,
            'maxAge' => $this->maxAge,
        ];
    }
}
