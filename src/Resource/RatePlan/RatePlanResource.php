<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
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

/** Age categories live under /settings/v1 but ship in the Rate Plan API spec, so they're grouped here. */
final readonly class RatePlanResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function ratePlans(): RatePlanDomainResource
    {
        return new RatePlanDomainResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function rates(): RateResource
    {
        return new RateResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function services(): ServiceResource
    {
        return new ServiceResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function companies(): CompanyResource
    {
        return new CompanyResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function cancellationPolicies(): CancellationPolicyResource
    {
        return new CancellationPolicyResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function noShowPolicies(): NoShowPolicyResource
    {
        return new NoShowPolicyResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function corporateCodes(): CorporateCodeResource
    {
        return new CorporateCodeResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function promoCodes(): PromoCodeResource
    {
        return new PromoCodeResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function ageCategories(): AgeCategoryResource
    {
        return new AgeCategoryResource($this->pipeline);
    }
}
