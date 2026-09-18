<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Types\Country\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class ListCountriesRequest extends Request
{
    protected Method $method = Method::GET;

    public function endpoint(): string
    {
        return '/inventory/v1/types/countries';
    }
}
