<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\CorporateCode\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListCorporateCodesRequest extends Request
{
    public function __construct(
        private ?string $propertyId = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/corporate-codes/codes';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
