<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListOrderedServicesRequest extends Request
{
    /** @param list<string> $serviceIds */
    public function __construct(
        private string $propertyId,
        private array $serviceIds,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/reports/v1/reports/ordered-services';
    }

    public function query(): array
    {
        return [
            'propertyId' => $this->propertyId,
            'serviceIds' => implode(',', $this->serviceIds),
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
        ];
    }
}
