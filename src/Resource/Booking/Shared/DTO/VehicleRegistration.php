<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class VehicleRegistration
{
    public function __construct(
        public ?string $number,
        public ?string $countryCode,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            number: ResponseData::nullableString($data, 'number'),
            countryCode: ResponseData::nullableString($data, 'countryCode'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'number' => $this->number,
            'countryCode' => $this->countryCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
