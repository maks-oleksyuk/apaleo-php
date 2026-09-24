<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateCityTaxRequest extends Request
{
    public function __construct(
        private string $cityTaxId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/settings/v1/city-tax/'.rawurlencode($this->cityTaxId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
