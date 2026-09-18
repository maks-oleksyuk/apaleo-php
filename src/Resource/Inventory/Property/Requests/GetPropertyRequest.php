<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class GetPropertyRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $propertyId,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/properties/'.rawurlencode($this->propertyId);
    }

    /** Requests every configured language so localized fields are always a full map, never account-dependent. */
    public function query(): array
    {
        return ['languages' => 'all'];
    }
}
