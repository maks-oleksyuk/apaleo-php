<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlanResource;
use Oleksyuk\Apaleo\Resource\Settings\CapturePolicy\CapturePolicyResource;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\CityTaxResource;
use Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\FeatureSettingsResource;
use Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\InvoiceAddressResource;
use Oleksyuk\Apaleo\Resource\Settings\Language\LanguageResource;
use Oleksyuk\Apaleo\Resource\Settings\MarketSegment\MarketSegmentResource;
use Oleksyuk\Apaleo\Resource\Settings\PropertySettings\PropertySettingsResource;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\SubAccountResource;
use Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\TimeSliceDefinitionResource;

/** Age categories also live under /settings/v1, but ship in the Rate Plan API spec, so they're in {@see RatePlanResource}. */
final readonly class SettingsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function capturePolicies(): CapturePolicyResource
    {
        return new CapturePolicyResource($this->pipeline);
    }

    public function cityTaxes(): CityTaxResource
    {
        return new CityTaxResource($this->pipeline);
    }

    public function subAccounts(): SubAccountResource
    {
        return new SubAccountResource($this->pipeline);
    }

    public function features(): FeatureSettingsResource
    {
        return new FeatureSettingsResource($this->pipeline);
    }

    public function invoiceAddresses(): InvoiceAddressResource
    {
        return new InvoiceAddressResource($this->pipeline);
    }

    public function languages(): LanguageResource
    {
        return new LanguageResource($this->pipeline);
    }

    public function marketSegments(): MarketSegmentResource
    {
        return new MarketSegmentResource($this->pipeline);
    }

    public function properties(): PropertySettingsResource
    {
        return new PropertySettingsResource($this->pipeline);
    }

    public function timeSliceDefinitions(): TimeSliceDefinitionResource
    {
        return new TimeSliceDefinitionResource($this->pipeline);
    }
}
