<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\PropertySettings\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetPropertySettingsRequest extends Request
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
        return '/settings/v1/properties/'.rawurlencode($this->propertyId);
    }
}
