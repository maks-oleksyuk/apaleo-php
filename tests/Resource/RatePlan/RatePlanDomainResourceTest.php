<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\RatePlan;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\CreateRatePlan;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\PricingRule;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\PriceAdjustmentType;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\RatePlanFilter;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ChannelCode;

/**
 * @internal
 *
 * @coversNothing
 */
final class RatePlanDomainResourceTest extends RatePlanTestCase
{
    public function testGetMapsResponseToDto(): void
    {
        $this->respond($this->fixture());

        $ratePlan = $this->api->ratePlans()->get('MUC-NONREF-SGL', ['property']);

        self::assertSame('Non Refundable', $ratePlan->name['en']);
        self::assertSame(GuaranteeType::Prepayment, $ratePlan->minGuaranteeType);
        self::assertSame(UnitGroupType::BedRoom, $ratePlan->unitGroup->type);
        self::assertSame(1, $ratePlan->cancellationPolicy->periodPriorToArrival?->days);
        self::assertSame([ChannelCode::Direct, ChannelCode::Unknown], $ratePlan->channelCodes);
        self::assertSame('MUC-FLEX-SGL', $ratePlan->pricingRule?->baseRatePlanId);
        self::assertSame('MUC-BRKF', $ratePlan->includedServices[0]->serviceId);
        self::assertSame('2027-12-31', $ratePlan->ratesRange?->to->format('Y-m-d'));
        self::assertNull($ratePlan->marketSegment);
        self::assertStringContainsString('expand=property', $this->lastUri());
        self::assertStringNotContainsString('languages=', $this->lastUri());
    }

    public function testListReadsEmbeddedServiceShapeAndSendsFilter(): void
    {
        $item = ['name' => 'Non Refundable', 'description' => 'No refunds', 'includedServices' => [['service' => ['id' => 'MUC-BRKF'], 'grossPrice' => ['amount' => 15, 'currency' => 'EUR']]]] + $this->fixture();
        $this->respond(['count' => 3, 'ratePlans' => [$item]]);

        $result = $this->api->ratePlans()->list(new RatePlanFilter(propertyId: 'MUC', channelCodes: [ChannelCode::Direct, ChannelCode::Ibe], derivationLevelFilter: ['lte_1']), pageSize: 1);

        self::assertSame(3, $result->totalCount);
        self::assertSame('Non Refundable', $result[0]->name);
        self::assertSame('No refunds', $result[0]->description);
        self::assertSame('MUC-BRKF', $result[0]->includedServices[0]->serviceId);
        self::assertStringContainsString('propertyId=MUC&channelCodes=Direct,Ibe&derivationLevelFilter=lte_1&pageSize=1', $this->lastUri());
    }

    public function testCreateSendsBody(): void
    {
        $this->respond(['id' => 'MUC-NEW'], 201);

        $id = $this->api->ratePlans()->create(new CreateRatePlan(
            code: 'NEW',
            propertyId: 'MUC',
            unitGroupId: 'MUC-SGL',
            cancellationPolicyId: 'MUC-FLEX',
            timeSliceDefinitionId: 'MUC-NIGHT',
            name: ['en' => 'New'],
            description: ['en' => 'New'],
            minGuaranteeType: GuaranteeType::PM6Hold,
            channelCodes: [ChannelCode::Direct],
            pricingRule: new PricingRule('MUC-FLEX-SGL', PriceAdjustmentType::Absolute, 5.0),
        ));

        self::assertSame('MUC-NEW', $id);
        self::assertSame([
            'code' => 'NEW',
            'propertyId' => 'MUC',
            'unitGroupId' => 'MUC-SGL',
            'cancellationPolicyId' => 'MUC-FLEX',
            'channelCodes' => ['Direct'],
            'timeSliceDefinitionId' => 'MUC-NIGHT',
            'name' => ['en' => 'New'],
            'description' => ['en' => 'New'],
            'minGuaranteeType' => 'PM6Hold',
            'pricingRule' => ['baseRatePlanId' => 'MUC-FLEX-SGL', 'type' => 'Absolute', 'value' => 5],
        ], $this->lastBody());
    }

    public function testBulkUpdateAndDeleteSendIdsInQuery(): void
    {
        $this->httpClient->addResponse(new Response(204));
        $this->api->ratePlans()->bulkUpdate(['A', 'B'], new JsonPatch()->replace('/name/en', 'X'));
        self::assertSame('PATCH', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/rateplan/v1/rate-plans?ratePlanIds=A,B', $this->lastUri());

        $this->httpClient->addResponse(new Response(204));
        $this->api->ratePlans()->bulkDelete(['A']);
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('ratePlanIds=A', $this->lastUri());
    }

    public function testExistsAndArchive(): void
    {
        $this->httpClient->addResponse(new Response(404));
        self::assertFalse($this->api->ratePlans()->exists('MISSING'));

        $this->httpClient->addResponse(new Response(204));
        $this->api->ratePlans()->archive('MUC-OLD');
        self::assertSame('PUT', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/rateplan/v1/rate-plan-actions/MUC-OLD/archive', $this->lastUri());
    }

    /** @return array<string, mixed> */
    private function fixture(): array
    {
        return [
            'id' => 'MUC-NONREF-SGL',
            'code' => 'NONREF',
            'name' => ['en' => 'Non Refundable'],
            'description' => ['en' => 'Non Refundable'],
            'minGuaranteeType' => 'Prepayment',
            'priceCalculationMode' => 'Truncate',
            'property' => ['id' => 'MUC'],
            'unitGroup' => ['id' => 'MUC-SGL', 'type' => 'BedRoom'],
            'cancellationPolicy' => ['id' => 'MUC-NONREF', 'periodPriorToArrival' => ['days' => 1]],
            'noShowPolicy' => ['id' => 'MUC-NOSHOW'],
            'channelCodes' => ['Direct', 'SomethingNew'],
            'timeSliceDefinition' => ['id' => 'MUC-NIGHT', 'name' => 'Overnight', 'template' => 'OverNight', 'checkInTime' => '17:00:00', 'checkOutTime' => '11:00:00'],
            'isBookable' => true,
            'isSubjectToCityTax' => true,
            'isDerived' => true,
            'derivationLevel' => 1,
            'pricingRule' => ['baseRatePlan' => ['id' => 'MUC-FLEX-SGL', 'isSubjectToCityTax' => true], 'type' => 'Percent', 'value' => -10],
            'includedServices' => [['serviceId' => 'MUC-BRKF', 'grossPrice' => ['amount' => 15, 'currency' => 'EUR'], 'pricingMode' => 'Included']],
            'ratesRange' => ['from' => '2026-01-01', 'to' => '2027-12-31'],
            'accountingConfigs' => [['serviceType' => 'Accommodation', 'vatType' => 'Reduced', 'validFrom' => '2026-01-01']],
        ];
    }
}
