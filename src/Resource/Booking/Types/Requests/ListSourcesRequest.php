<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Types\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListSourcesRequest extends Request
{
    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/types/sources';
    }
}
