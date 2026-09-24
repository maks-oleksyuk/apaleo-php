<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\FeatureSettings;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\DTO\FeatureSettings;
use Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\Requests\GetFeatureSettingsRequest;
use Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\Requests\UpdateFeatureSettingsRequest;

final readonly class FeatureSettingsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $propertyId): FeatureSettings
    {
        $data = $this->pipeline->send(new GetFeatureSettingsRequest($propertyId));

        return FeatureSettings::fromArray($data);
    }

    public function update(string $propertyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateFeatureSettingsRequest($propertyId, $patch));
    }
}
