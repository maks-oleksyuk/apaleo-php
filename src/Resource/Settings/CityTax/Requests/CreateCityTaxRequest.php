<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO\CreateCityTax;

final readonly class CreateCityTaxRequest extends Request
{
    public function __construct(
        private CreateCityTax $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/settings/v1/city-tax';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
