<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Service\Enum\PricingUnit;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\AccountingConfig;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ChannelCode;

final readonly class CreateService
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description localized
     * @param list<ChannelCode> $channelCodes channels the service is sold on as an extra; empty = not sold as an extra
     * @param list<AccountingConfig> $accountingConfigs
     */
    public function __construct(
        public string $code,
        public string $propertyId,
        public array $name,
        public array $description,
        public MonetaryValue $defaultGrossPrice,
        public PricingUnit $pricingUnit,
        public bool $postNextDay,
        public ?ServiceAvailability $availability = null,
        public array $channelCodes = [],
        public array $accountingConfigs = [],
        public ?string $ageCategoryId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'description' => $this->description,
            'defaultGrossPrice' => $this->defaultGrossPrice->toArray(),
            'pricingUnit' => $this->pricingUnit->value,
            'postNextDay' => $this->postNextDay,
            'availability' => $this->availability?->toArray(),
            'channelCodes' => array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCodes) ?: null,
            'accountingConfigs' => array_map(static fn (AccountingConfig $a): array => $a->toArray(), $this->accountingConfigs) ?: null,
            'ageCategoryId' => $this->ageCategoryId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
