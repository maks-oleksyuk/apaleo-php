<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\PropertySettings;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\PropertySettings\DTO\PropertySettings;
use Oleksyuk\Apaleo\Resource\Settings\PropertySettings\Requests\GetPropertySettingsRequest;

final readonly class PropertySettingsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $propertyId): PropertySettings
    {
        $data = $this->pipeline->send(new GetPropertySettingsRequest($propertyId));

        return PropertySettings::fromArray($data);
    }
}
