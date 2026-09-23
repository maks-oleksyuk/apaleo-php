<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\AgeCategoryResource;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\CancellationPolicyResource;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\CompanyResource;
use Oleksyuk\Apaleo\Resource\RatePlan\CorporateCode\CorporateCodeResource;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\NoShowPolicyResource;
use Oleksyuk\Apaleo\Resource\RatePlan\PromoCode\PromoCodeResource;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\RateResource;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\RatePlanDomainResource;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\ServiceResource;

/**
 * Aggregates the Rate Plan API's sub-resources (rate plans, rates, services, companies, cancellation
 * and no-show policies, corporate and promo codes, age categories). Age categories live under
 * /settings/v1 but ship in the same API spec, so they're grouped here.
 */
final readonly class RatePlanResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function ratePlans(): RatePlanDomainResource
    {
        return new RatePlanDomainResource($this->pipeline);
    }

    public function rates(): RateResource
    {
        return new RateResource($this->pipeline);
    }

    public function services(): ServiceResource
    {
        return new ServiceResource($this->pipeline);
    }

    public function companies(): CompanyResource
    {
        return new CompanyResource($this->pipeline);
    }

    public function cancellationPolicies(): CancellationPolicyResource
    {
        return new CancellationPolicyResource($this->pipeline);
    }

    public function noShowPolicies(): NoShowPolicyResource
    {
        return new NoShowPolicyResource($this->pipeline);
    }

    public function corporateCodes(): CorporateCodeResource
    {
        return new CorporateCodeResource($this->pipeline);
    }

    public function promoCodes(): PromoCodeResource
    {
        return new PromoCodeResource($this->pipeline);
    }

    public function ageCategories(): AgeCategoryResource
    {
        return new AgeCategoryResource($this->pipeline);
    }
}
