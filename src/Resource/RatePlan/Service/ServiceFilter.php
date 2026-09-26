<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ServiceFilter
{
    /**
     * @param list<ChannelCode> $channelCodes
     * @param list<ServiceType> $serviceTypes
     */
    public function __construct(
        public ?string $propertyId = null,
        public ?string $textSearch = null,
        public ?bool $onlySoldAsExtras = null,
        public array $channelCodes = [],
        public array $serviceTypes = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'textSearch' => $this->textSearch,
            'onlySoldAsExtras' => $this->onlySoldAsExtras,
            'channelCodes' => Query::csv($this->channelCodes),
            'serviceTypes' => Query::csv($this->serviceTypes),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
