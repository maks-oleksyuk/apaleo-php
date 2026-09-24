<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\PropertySettings\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertySettings
{
    public function __construct(
        public string $timeZone,
        public string $currency,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            timeZone: ResponseData::string($data, 'timeZone'),
            currency: ResponseData::string($data, 'currency'),
        );
    }
}
