<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Types\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListFinanceTypesRequest extends Request
{
    /** @param 'currencies'|'payment-methods'|'service-types' $type */
    public function __construct(
        private string $type,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/types/'.$this->type;
    }
}
