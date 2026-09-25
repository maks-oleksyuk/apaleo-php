<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\RatePlan;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO\CreateAgeCategory;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CreateCancellationPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\Enum\CancellationPolicyReference;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\FeeDetails;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\PercentValue;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\Period;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;

/**
 * @internal
 *
 * @coversNothing
 */
final class PolicyAndCodeResourceTest extends RatePlanTestCase
{
    public function testCancellationPolicyGetAndCreate(): void
    {
        $this->respond([
            'id' => 'MUC-FLEX', 'code' => 'FLEX', 'propertyId' => 'MUC',
            'name' => ['en' => 'Flexible'], 'description' => ['en' => 'Free until 1 day before'],
            'periodFromReference' => ['days' => 1], 'reference' => 'PriorToArrival',
            'fee' => ['vatType' => 'Without', 'percentValue' => ['percent' => 100, 'limit' => 1]],
        ]);

        $policy = $this->api->cancellationPolicies()->get('MUC-FLEX');

        self::assertSame(CancellationPolicyReference::PriorToArrival, $policy->reference);
        self::assertSame(100, $policy->fee->percentValue?->percent);
        self::assertNull($policy->fee->fixedValue);
        self::assertStringEndsWith('/rateplan/v1/cancellation-policies/MUC-FLEX', $this->lastUri());

        $this->respond(['id' => 'MUC-FLEX'], 201);
        $this->api->cancellationPolicies()->create(new CreateCancellationPolicy(
            code: 'FLEX',
            propertyId: 'MUC',
            name: ['en' => 'Flexible'],
            description: ['en' => 'Flexible'],
            reference: CancellationPolicyReference::PriorToArrival,
            fee: new FeeDetails(VatType::Without, percentValue: new PercentValue(100, 1)),
            periodFromReference: new Period(days: 1),
        ));

        $body = $this->lastBody();
        self::assertSame(['days' => 1], $body['periodFromReference']);
        self::assertSame(['vatType' => 'Without', 'percentValue' => ['percent' => 100, 'limit' => 1]], $body['fee']);
    }

    public function testNoShowPolicyListAndUpdate(): void
    {
        $this->respond(['count' => 1, 'noShowPolicies' => [[
            'id' => 'MUC-NS', 'code' => 'NS', 'propertyId' => 'MUC', 'name' => 'No-show', 'description' => 'Full stay',
            'fee' => ['vatType' => 'Without', 'fixedValue' => ['amount' => 50, 'currency' => 'EUR']],
        ]]]);

        $policies = $this->api->noShowPolicies()->list('MUC', pageSize: 10);

        self::assertSame(50.0, $policies[0]->fee->fixedValue?->amount);
        self::assertStringEndsWith('?propertyId=MUC&pageSize=10', $this->lastUri());

        $this->httpClient->addResponse(new Response(204));
        $this->api->noShowPolicies()->update('MUC-NS', new JsonPatch()->replace('/code', 'NS2'));
        self::assertSame('PATCH', $this->lastRequest()->getMethod());
    }

    public function testCodesAndAgeCategories(): void
    {
        $this->respond(['count' => 1, 'corporateCodes' => [['code' => 'ACME1', 'companyId' => 'MUC-ACME', 'companyCode' => 'ACME', 'companyName' => 'Acme', 'ratePlanId' => 'MUC-CORP']]]);
        self::assertSame('MUC-CORP', $this->api->corporateCodes()->list('MUC')[0]->ratePlanId);
        self::assertStringContainsString('/rateplan/v1/corporate-codes/codes?propertyId=MUC', $this->lastUri());

        $this->respond(['count' => 1, 'promoCodes' => [['code' => 'SUMMER', 'relatedRateplanIds' => ['MUC-FLEX']]]]);
        self::assertSame(['MUC-FLEX'], $this->api->promoCodes()->list()[0]->relatedRatePlanIds);

        $this->respond(['count' => 1, 'ageCategories' => [['id' => 'MUC-CHILD', 'code' => 'CHILD', 'propertyId' => 'MUC', 'name' => 'Child', 'minAge' => 3, 'maxAge' => 12]]]);
        $categories = $this->api->ageCategories()->list('MUC');
        self::assertSame(12, $categories[0]->maxAge);
        self::assertStringEndsWith('/settings/v1/age-categories?propertyId=MUC', $this->lastUri());

        $this->respond(['id' => 'MUC-BABY'], 201);
        self::assertSame('MUC-BABY', $this->api->ageCategories()->create(new CreateAgeCategory('BABY', 'MUC', ['en' => 'Baby'], 0, 2)));
    }
}
