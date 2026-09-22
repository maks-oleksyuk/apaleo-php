<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Types\Country\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListCountriesRequest extends Request
{
    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/types/countries';
    }
}
