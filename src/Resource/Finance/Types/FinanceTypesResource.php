<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Types;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\VatRate;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Finance\Types\Requests\ListFinanceTypesRequest;
use Oleksyuk\Apaleo\Resource\Finance\Types\Requests\ListVatTypesRequest;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class FinanceTypesResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return list<string> ISO 4217 codes */
    public function currencies(): array
    {
        return ResponseData::stringListOrEmpty($this->pipeline->send(new ListFinanceTypesRequest('currencies')), 'isoCurrencies');
    }

    /** @return list<PaymentMethod> */
    public function paymentMethods(): array
    {
        $data = $this->pipeline->send(new ListFinanceTypesRequest('payment-methods'));

        return array_map(PaymentMethod::fromApi(...), ResponseData::stringListOrEmpty($data, 'paymentMethods'));
    }

    /** @return list<FinanceServiceType> */
    public function serviceTypes(): array
    {
        $data = $this->pipeline->send(new ListFinanceTypesRequest('service-types'));

        return array_map(FinanceServiceType::fromApi(...), ResponseData::stringListOrEmpty($data, 'serviceTypes'));
    }

    /**
     * @param ?\DateTimeImmutable $atDate the rates valid on that day; today when omitted
     *
     * @return list<VatRate>
     */
    public function vatTypes(string $isoCountryCode, ?\DateTimeImmutable $atDate = null): array
    {
        $data = $this->pipeline->send(new ListVatTypesRequest($isoCountryCode, $atDate));

        return array_map(VatRate::fromArray(...), ResponseData::nestedList($data, 'vatTypes'));
    }
}
