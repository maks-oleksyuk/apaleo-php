<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class SubAccount
{
    public function __construct(
        public string $id,
        public string $propertyId,
        public string $code,
        public string $name,
        public ServiceType $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            propertyId: ResponseData::string($data, 'propertyId'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            type: ServiceType::fromApi(ResponseData::string($data, 'type')),
        );
    }
}
