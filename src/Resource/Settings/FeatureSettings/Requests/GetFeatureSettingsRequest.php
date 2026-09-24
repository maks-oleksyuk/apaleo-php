<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetFeatureSettingsRequest extends Request
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
        return '/settings/v1/features/'.rawurlencode($this->propertyId);
    }
}
