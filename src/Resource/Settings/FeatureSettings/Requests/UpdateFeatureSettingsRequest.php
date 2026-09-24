<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateFeatureSettingsRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/settings/v1/features/'.rawurlencode($this->propertyId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
