<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** A company allowed to book the rate plan; $code and $name are only filled in on read. */
final readonly class RatePlanCompany
{
    public function __construct(
        public string $id,
        public ?string $corporateCode = null,
        public ?string $code = null,
        public ?string $name = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            code: ResponseData::nullableString($data, 'code'),
            name: ResponseData::nullableString($data, 'name'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter(['id' => $this->id, 'corporateCode' => $this->corporateCode], static fn (mixed $value): bool => $value !== null);
    }
}
