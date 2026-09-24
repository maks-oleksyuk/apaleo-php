<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;

final readonly class CreateSubAccount
{
    /** @param string $code unique per property; apaleo rejects its reserved codes (e.g. "Unspecified") */
    public function __construct(
        public string $propertyId,
        public string $code,
        public string $name,
        public ServiceType $type,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'propertyId' => $this->propertyId,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type->value,
        ];
    }
}
