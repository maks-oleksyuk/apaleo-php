<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** Shared shape for BlockUnitsModel and BlockServicesModel — identical fields, different meaning (units vs. services). */
final readonly class BlockCounts
{
    public function __construct(
        public int $definite,
        public int $tentative,
        public int $optional,
        public int $optionalDeducting,
        public int $picked,
        public int $remaining,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            definite: ResponseData::int($data, 'definite'),
            tentative: ResponseData::int($data, 'tentative'),
            optional: ResponseData::int($data, 'optional'),
            optionalDeducting: ResponseData::int($data, 'optionalDeducting'),
            picked: ResponseData::int($data, 'picked'),
            remaining: ResponseData::int($data, 'remaining'),
        );
    }
}
