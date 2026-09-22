<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetArrivalsReportRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private int $month,
        private int $year,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/reports/v1/reports/arrivals';
    }

    public function query(): array
    {
        return [
            'propertyId' => $this->propertyId,
            'month' => $this->month,
            'year' => $this->year,
        ];
    }
}
