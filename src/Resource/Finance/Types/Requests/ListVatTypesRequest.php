<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Types\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListVatTypesRequest extends Request
{
    public function __construct(
        private string $isoCountryCode,
        private ?\DateTimeImmutable $atDate = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/types/vat';
    }

    public function query(): array
    {
        return array_filter([
            'isoCountryCode' => $this->isoCountryCode,
            'atDate' => $this->atDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
