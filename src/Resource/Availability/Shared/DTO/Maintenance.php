<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Maintenance
{
    public function __construct(
        public int $outOfService,
        public int $outOfOrder,
        public int $outOfInventory,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            outOfService: ResponseData::int($data, 'outOfService'),
            outOfOrder: ResponseData::int($data, 'outOfOrder'),
            outOfInventory: ResponseData::int($data, 'outOfInventory'),
        );
    }
}
