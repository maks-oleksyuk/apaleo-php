<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Which charges a routing moves: all given criteria must match. $from/$to bound the service date (inclusive). */
final readonly class RoutingChargeFilter
{
    /**
     * @param list<string>               $folioIds      source folios
     * @param array<string, string>      $subAccounts   id => name
     * @param list<FinanceServiceType>   $serviceTypes
     * @param array<string, string>      $services      id => name
     */
    public function __construct(
        public array $folioIds,
        public array $subAccounts,
        public array $serviceTypes,
        public array $services,
        public ?\DateTimeImmutable $from,
        public ?\DateTimeImmutable $to,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            folioIds: ResponseData::stringListOrEmpty($data, 'folioIds'),
            subAccounts: self::namesById(ResponseData::nestedList($data, 'subAccounts')),
            serviceTypes: array_map(FinanceServiceType::fromApi(...), ResponseData::stringListOrEmpty($data, 'serviceTypes')),
            services: self::namesById(ResponseData::nestedList($data, 'services')),
            from: ResponseData::nullableDate($data, 'from'),
            to: ResponseData::nullableDate($data, 'to'),
        );
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return array<string, string>
     */
    private static function namesById(array $items): array
    {
        $names = [];
        foreach ($items as $item) {
            $names[ResponseData::string($item, 'id')] = ResponseData::string($item, 'name');
        }

        return $names;
    }
}
