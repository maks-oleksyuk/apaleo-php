<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO;

use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\RemittanceResponsibility;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Support\ResponseData;

/** One `ignoredFor` entry: charges from this channel (and optionally these sources) aren't taxed. Flattens the API's `{distributionChannel: {...}}` wrapper. */
final readonly class CityTaxIgnoreRule
{
    /** @param list<string> $sources */
    public function __construct(
        public ChannelCode $channelCode,
        public array $sources = [],
        public ?RemittanceResponsibility $remittanceResponsibility = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $channel = ResponseData::nested($data, 'distributionChannel');
        $remittance = ResponseData::nullableString($channel, 'remittanceResponsibility');

        return new self(
            channelCode: ChannelCode::fromApi(ResponseData::string($channel, 'channelCode')),
            sources: ResponseData::stringListOrEmpty($channel, 'sources'),
            remittanceResponsibility: $remittance !== null ? RemittanceResponsibility::fromApi($remittance) : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['distributionChannel' => array_filter([
            'channelCode' => $this->channelCode->value,
            'sources' => $this->sources ?: null,
            'remittanceResponsibility' => $this->remittanceResponsibility?->value,
        ], static fn (mixed $value): bool => $value !== null)];
    }
}
