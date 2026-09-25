<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;

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
            'channelCodes' => implode(',', array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCodes)) ?: null,
            'serviceTypes' => implode(',', array_map(static fn (ServiceType $t): string => $t->value, $this->serviceTypes)) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
