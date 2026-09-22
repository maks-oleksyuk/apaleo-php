<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Reports\Enum\ExportAccountType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ExportAccount
{
    public function __construct(
        public string $name,
        public string $number,
        public ?string $parentNumber,
        public ExportAccountType $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: ResponseData::string($data, 'name'),
            number: ResponseData::string($data, 'number'),
            parentNumber: ResponseData::nullableString($data, 'parentNumber'),
            type: ExportAccountType::fromApi(ResponseData::string($data, 'type')),
        );
    }
}
