<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class CountSubAccountsRequest extends Request
{
    public function __construct(
        private string $propertyId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/settings/v1/sub-accounts/$count';
    }

    public function query(): array
    {
        return ['propertyId' => $this->propertyId];
    }
}
