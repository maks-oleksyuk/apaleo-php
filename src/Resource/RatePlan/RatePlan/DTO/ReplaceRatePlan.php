<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\PriceCalculationMode;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\AccountingConfig;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ChannelCode;

/** A full replacement: anything left out is cleared. */
final readonly class ReplaceRatePlan
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description localized
     * @param list<ChannelCode> $channelCodes
     * @param list<string> $promoCodes
     * @param list<BookingPeriod> $bookingPeriods
     * @param list<IncludedService> $includedServices
     * @param list<RatePlanCompany> $companies
     * @param list<Surcharge> $surcharges
     * @param list<RatePlanAgeCategory> $ageCategories
     * @param list<AccountingConfig> $accountingConfigs
     */
    public function __construct(
        public array $name,
        public array $description,
        public GuaranteeType $minGuaranteeType,
        public string $cancellationPolicyId,
        public array $channelCodes,
        public ?string $noShowPolicyId = null,
        public ?PriceCalculationMode $priceCalculationMode = null,
        public array $promoCodes = [],
        public ?bool $isSubjectToCityTax = null,
        public array $bookingPeriods = [],
        public ?BookingRestrictions $restrictions = null,
        public array $includedServices = [],
        public array $companies = [],
        public ?PricingRule $pricingRule = null,
        public array $surcharges = [],
        public array $ageCategories = [],
        public array $accountingConfigs = [],
        public ?string $marketSegmentId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'minGuaranteeType' => $this->minGuaranteeType->value,
            'priceCalculationMode' => $this->priceCalculationMode?->value,
            'channelCodes' => array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCodes),
            'promoCodes' => $this->promoCodes ?: null,
            'isSubjectToCityTax' => $this->isSubjectToCityTax,
            'cancellationPolicyId' => $this->cancellationPolicyId,
            'noShowPolicyId' => $this->noShowPolicyId,
            'bookingPeriods' => array_map(static fn (BookingPeriod $p): array => $p->toArray(), $this->bookingPeriods) ?: null,
            'restrictions' => $this->restrictions?->toArray(),
            'includedServices' => array_map(static fn (IncludedService $s): array => $s->toArray(), $this->includedServices) ?: null,
            'companies' => array_map(static fn (RatePlanCompany $c): array => $c->toArray(), $this->companies) ?: null,
            'pricingRule' => $this->pricingRule?->toArray(),
            'surcharges' => array_map(static fn (Surcharge $s): array => $s->toArray(), $this->surcharges) ?: null,
            'ageCategories' => array_map(static fn (RatePlanAgeCategory $a): array => $a->toArray(), $this->ageCategories) ?: null,
            'accountingConfigs' => array_map(static fn (AccountingConfig $a): array => $a->toArray(), $this->accountingConfigs) ?: null,
            'marketSegmentId' => $this->marketSegmentId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
