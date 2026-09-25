<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\RatePlan;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO\AgeCategory;
use Oleksyuk\Apaleo\Resource\RatePlan\CancellationPolicy\DTO\CancellationPolicyListItem;
use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\Company;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO\CreateNoShowPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\NoShowPolicy\DTO\NoShowPolicy;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\ReplaceRatePlan;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\RatePlanFilter;
use Oleksyuk\Apaleo\Tests\Support\Fixture;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * Get/update/delete/exists/count round-trips; responses are built from the DTO constructors by Fixture.
 *
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\RatePlan')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class RatePlanCrudTest extends RatePlanTestCase
{
    public function testAgeCategoryGetUpdateDelete(): void
    {
        $this->respond(Fixture::response(AgeCategory::class));
        self::assertSame('x', $this->api->ageCategories()->get('AC1', ['en'])->code);
        self::assertStringContainsString('AC1', $this->lastUri());

        $this->respondEmpty();
        $this->api->ageCategories()->update('AC1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->ageCategories()->delete('AC1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testCancellationPolicyListUpdateDelete(): void
    {
        $this->respond(['cancellationPolicies' => [Fixture::response(CancellationPolicyListItem::class)], 'count' => 1]);
        $result = $this->api->cancellationPolicies()->list('MUC', 1, 10);
        self::assertSame(1, $result->totalCount);
        self::assertCount(1, $result->items);

        $this->respondEmpty();
        $this->api->cancellationPolicies()->update('CP1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->cancellationPolicies()->delete('CP1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testCompanyGetUpdateDelete(): void
    {
        $this->respond(Fixture::response(Company::class));
        self::assertInstanceOf(Company::class, $this->api->companies()->get('C1'));

        $this->respondEmpty();
        $this->api->companies()->update('C1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->companies()->delete('C1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testNoShowPolicyGetCreateDelete(): void
    {
        $this->respond(Fixture::response(NoShowPolicy::class));
        self::assertInstanceOf(NoShowPolicy::class, $this->api->noShowPolicies()->get('NS1', ['en']));

        $this->respond(['id' => 'NS2']);
        self::assertSame('NS2', $this->api->noShowPolicies()->create(Fixture::object(CreateNoShowPolicy::class), 'key-1'));
        self::assertSame('key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));

        $this->respondEmpty();
        $this->api->noShowPolicies()->delete('NS2');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testRatePlanExistsCountReplaceDelete(): void
    {
        $this->httpClient->addResponse(new Response(200));
        $this->httpClient->addResponse(new Response(404));
        self::assertTrue($this->api->ratePlans()->exists('MUC-RP'));
        self::assertFalse($this->api->ratePlans()->exists('MUC-XX'));

        $this->respond(['count' => 4]);
        self::assertSame(4, $this->api->ratePlans()->count(new RatePlanFilter()));

        $this->respondEmpty();
        $this->api->ratePlans()->replace('MUC-RP', Fixture::object(ReplaceRatePlan::class));
        self::assertSame('PUT', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->ratePlans()->delete('MUC-RP');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testServiceExistsCountUpdateDelete(): void
    {
        $this->httpClient->addResponse(new Response(200));
        $this->httpClient->addResponse(new Response(404));
        self::assertTrue($this->api->services()->exists('S1'));
        self::assertFalse($this->api->services()->exists('S2'));

        $this->respond(['count' => 2]);
        self::assertSame(2, $this->api->services()->count());

        $this->respondEmpty();
        $this->api->services()->update('S1', $this->patch());
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->respondEmpty();
        $this->api->services()->delete('S1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    private function patch(): JsonPatch
    {
        return new JsonPatch()->replace('/name', 'x');
    }

    private function respondEmpty(): void
    {
        $this->httpClient->addResponse(new Response(204));
    }
}
