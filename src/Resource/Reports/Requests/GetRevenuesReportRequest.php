<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetRevenuesReportRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private ?string $languageCode = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/reports/v1/reports/revenues';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'languageCode' => $this->languageCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
