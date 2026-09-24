<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing;

final readonly class RoutingFilter
{
    /**
     * @param list<string> $routingIds
     * @param list<string> $bookingIds
     * @param list<string> $propertyIds
     * @param list<string> $sourceFolioIds
     * @param list<string> $destinationFolioIds
     */
    public function __construct(
        public array $routingIds = [],
        public array $bookingIds = [],
        public array $propertyIds = [],
        public array $sourceFolioIds = [],
        public array $destinationFolioIds = [],
    ) {}

    /** @return array<string, string> */
    public function toQuery(): array
    {
        return array_filter([
            'routingIds' => implode(',', $this->routingIds),
            'bookingIds' => implode(',', $this->bookingIds),
            'propertyIds' => implode(',', $this->propertyIds),
            'sourceFolioIds' => implode(',', $this->sourceFolioIds),
            'destinationFolioIds' => implode(',', $this->destinationFolioIds),
        ], static fn (string $value): bool => $value !== '');
    }
}
