<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class EmbeddedCompany
{
    public function __construct(
        public string $id,
        public ?string $code,
        public ?string $name,
        public ?bool $canCheckOutOnAr,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::nullableString($data, 'code'),
            name: ResponseData::nullableString($data, 'name'),
            canCheckOutOnAr: \array_key_exists('canCheckOutOnAr', $data) ? ResponseData::bool($data, 'canCheckOutOnAr') : null,
        );
    }
}
