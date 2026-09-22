<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Service\DTO;

use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\BlockCounts;
use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\EmbeddedService;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceAvailabilityItem
{
    public function __construct(
        public EmbeddedService $service,
        public int $quantity,
        public int $soldCount,
        public int $availableCount,
        public \DateTimeImmutable $serviceDate,
        public BlockCounts $block,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            service: EmbeddedService::fromArray(ResponseData::nested($data, 'service')),
            quantity: ResponseData::int($data, 'quantity'),
            soldCount: ResponseData::int($data, 'soldCount'),
            availableCount: ResponseData::int($data, 'availableCount'),
            serviceDate: ResponseData::dateTime($data, 'serviceDate'),
            block: BlockCounts::fromArray(ResponseData::nested($data, 'block')),
        );
    }
}
