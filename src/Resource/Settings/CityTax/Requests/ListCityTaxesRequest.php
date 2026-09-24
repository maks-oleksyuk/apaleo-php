<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListCityTaxesRequest extends Request
{
    public function __construct(
        private ?string $propertyId = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/city-tax';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
