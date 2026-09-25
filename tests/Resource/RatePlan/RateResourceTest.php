<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\RatePlan;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\RatePatch;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\RateRestrictions;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\ReplaceRate;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\DayOfWeek;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\RatePlan')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class RateResourceTest extends RatePlanTestCase
{
    public function testListMapsRatesAndSendsDateRange(): void
    {
        $this->respond(['count' => 2, 'rates' => [
            [
                'from' => '2026-09-23T17:00:00+02:00',
                'to' => '2026-09-24T11:00:00+02:00',
                'price' => ['amount' => 100, 'currency' => 'EUR'],
                'calculatedPrices' => [['adults' => 2, 'price' => ['amount' => 120, 'currency' => 'EUR']]],
                'restrictions' => ['minLengthOfStay' => 2, 'closed' => false, 'closedOnArrival' => true, 'closedOnDeparture' => false],
            ],
            ['from' => '2026-09-24T17:00:00+02:00', 'to' => '2026-09-25T11:00:00+02:00'],
        ]]);

        $rates = $this->api->rates()->list('MUC-FLEX', new \DateTimeImmutable('2026-09-23 15:00'), new \DateTimeImmutable('2026-09-25'));

        self::assertSame(100.0, $rates[0]->price?->amount);
        self::assertSame(120.0, $rates[0]->calculatedPrices[0]->price->amount);
        self::assertTrue($rates[0]->restrictions?->closedOnArrival);
        self::assertNull($rates[1]->price);
        self::assertStringEndsWith('/rateplan/v1/rate-plans/MUC-FLEX/rates?from=2026-09-23&to=2026-09-25', $this->lastUri());
    }

    public function testReplaceSendsRatesWrapped(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->api->rates()->replace('MUC-FLEX', [new ReplaceRate(
            new \DateTimeImmutable('2026-09-23T17:00:00+02:00'),
            new \DateTimeImmutable('2026-09-24T11:00:00+02:00'),
            new MonetaryValue(99.5, 'EUR'),
            new RateRestrictions(minLengthOfStay: 2),
        )]);

        self::assertSame('PUT', $this->lastRequest()->getMethod());
        self::assertSame(['rates' => [[
            'from' => '2026-09-23T17:00:00+02:00',
            'to' => '2026-09-24T11:00:00+02:00',
            'price' => ['amount' => 99.5, 'currency' => 'EUR'],
            'restrictions' => ['minLengthOfStay' => 2, 'closed' => false, 'closedOnArrival' => false, 'closedOnDeparture' => false],
        ]]], $this->lastBody());
    }

    public function testUpdateSendsPatchesPerRange(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->api->rates()->update('MUC-FLEX', [new RatePatch(
            new \DateTimeImmutable('2026-10-01'),
            new \DateTimeImmutable('2026-10-31'),
            new JsonPatch()->replace('/price/amount', 80),
            [DayOfWeek::Saturday],
        )]);

        self::assertSame(['rates' => [[
            'from' => '2026-10-01',
            'to' => '2026-10-31',
            'weekDays' => ['Saturday'],
            'operations' => [['op' => 'replace', 'path' => '/price/amount', 'value' => 80]],
        ]]], $this->lastBody());
    }

    public function testBulkUpdateSendsFiltersInQueryAndPatchAsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->api->rates()->bulkUpdate(['A', 'B'], new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-02'), new JsonPatch()->remove('/restrictions'), [DayOfWeek::Monday, DayOfWeek::Friday]);

        self::assertStringEndsWith('/rateplan/v1/rates?ratePlanIds=A,B&from=2026-10-01&to=2026-10-02&weekDays=Monday,Friday', $this->lastUri());
        self::assertSame([['op' => 'remove', 'path' => '/restrictions']], $this->lastBody());
    }

    public function testCountAndDelete(): void
    {
        $this->respond(['count' => 30]);
        self::assertSame(30, $this->api->rates()->count('MUC-FLEX', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31')));
        self::assertStringContainsString('/rates/$count?from=2026-10-01', $this->lastUri());

        $this->httpClient->addResponse(new Response(204));
        $this->api->rates()->delete('MUC-FLEX', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31'));
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }
}
