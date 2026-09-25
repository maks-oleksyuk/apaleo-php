<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Reports;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Resource\Reports\Enum\Gender;
use Oleksyuk\Apaleo\Resource\Reports\Enum\Title;
use Oleksyuk\Apaleo\Resource\Reports\ReportsResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Reports')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class ReportsResourceTest extends TestCase
{
    use MockPipeline;

    private ReportsResource $reports;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->reports = new ReportsResource($pipeline);
    }

    public function testOrderedServicesMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'orderedServices' => [[
                'id' => 'MUC-BRKF-1', 'code' => 'BRKF', 'name' => 'Breakfast',
                'serviceDate' => '2026-09-20', 'count' => 2,
                'guest' => ['title' => 'Mr', 'gender' => 'Male', 'firstName' => 'John', 'lastName' => 'Doe'],
                'reservation' => ['id' => 'XPGMSXGF-1', 'arrival' => '2026-09-20T14:00:00+02:00', 'departure' => '2026-09-22T11:00:00+02:00', 'persons' => 2],
                'unit' => ['id' => 'MUC-MTA', 'name' => 'A.101'],
                'unitGroup' => ['id' => 'MUC-SGL', 'code' => 'SGL', 'name' => 'Single Room'],
            ]],
        ])));

        $result = $this->reports->orderedServices('MUC', ['MUC-BRKF'], new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'));

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);
        $guest = $result->items[0]->guest;
        self::assertNotNull($guest);
        self::assertSame(Title::Mr, $guest->title);
        self::assertSame(Gender::Male, $guest->gender);

        $unit = $result->items[0]->unit;
        self::assertNotNull($unit);
        self::assertSame('MUC-MTA', $unit->id);
        self::assertSame('MUC-SGL', $result->items[0]->unitGroup->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
        self::assertStringContainsString('serviceIds=MUC-BRKF', (string) $request->getUri());
    }

    public function testArrivalsMapsResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'total' => 10, 'totalAdults' => 8, 'totalChildren' => 2,
            'travelPurposeBreakdown' => [['purpose' => 'Business', 'number' => 6, 'percent' => 60.0, 'reservationIds' => ['R1', 'R2']]],
            'nationalityBreakdown' => [['countryCode' => 'DE', 'number' => 10, 'percent' => 100.0, 'reservationIds' => ['R1']]],
            'countryOfResidenceBreakdown' => [],
        ])));

        $result = $this->reports->arrivals('MUC', 9, 2026);

        self::assertSame(10, $result->total);
        self::assertSame(6, $result->travelPurposeBreakdown[0]->number);
        self::assertSame(['R1', 'R2'], $result->travelPurposeBreakdown[0]->reservationIds);
        self::assertSame('DE', $result->nationalityBreakdown[0]->countryCode);

        $request = $this->lastRequest();
        self::assertStringContainsString('month=9', (string) $request->getUri());
        self::assertStringContainsString('year=2026', (string) $request->getUri());
    }

    public function testPropertyPerformanceMapsResponse(): void
    {
        $metrics = [
            'houseCount' => 10, 'houseItemsCount' => 10, 'soldCount' => 6, 'soldItemsCount' => 6,
            'unsoldCount' => 4, 'unsoldItemsCount' => 4, 'outOfOrderCount' => 0, 'outOfOrderItemsCount' => 0,
            'tentativelyBlockedCount' => 0, 'tentativelyBlockedItemsCount' => 0, 'definitelyBlockedCount' => 0,
            'optionallyBlockedCount' => 0, 'arrivalsCount' => 2, 'departuresCount' => 1, 'noShowsCount' => 0,
            'cancellationsCount' => 0, 'occupancyPercentage' => 60.0,
            'grossUnitRevenue' => ['amount' => 100.0, 'currency' => 'EUR'],
            'netUnitRevenue' => ['amount' => 90.0, 'currency' => 'EUR'],
            'grossAccommodationRevenue' => ['amount' => 100.0, 'currency' => 'EUR'],
            'netAccommodationRevenue' => ['amount' => 90.0, 'currency' => 'EUR'],
            'grossFoodAndBeveragesRevenue' => ['amount' => 0.0, 'currency' => 'EUR'],
            'netFoodAndBeveragesRevenue' => ['amount' => 0.0, 'currency' => 'EUR'],
            'grossOtherRevenue' => ['amount' => 0.0, 'currency' => 'EUR'],
            'netOtherRevenue' => ['amount' => 0.0, 'currency' => 'EUR'],
            'grossAdr' => ['amount' => 50.0, 'currency' => 'EUR'],
            'netAdr' => ['amount' => 45.0, 'currency' => 'EUR'],
            'revPar' => ['amount' => 30.0, 'currency' => 'EUR'],
        ];

        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            ...$metrics,
            'businessDays' => [[
                ...$metrics,
                'businessDay' => '2026-09-20',
                'unitGroups' => [[
                    ...$metrics,
                    'unitGroup' => ['id' => 'MUC-SGL', 'type' => 'BedRoom'],
                ]],
            ]],
        ])));

        $result = $this->reports->propertyPerformance('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-21'));

        self::assertSame(6, $result->metrics->soldCount);
        self::assertSame(30.0, $result->metrics->revPar->amount);
        self::assertCount(1, $result->businessDays);
        self::assertSame('MUC-SGL', $result->businessDays[0]->unitGroups[0]->unitGroup->id);
        self::assertSame(6, $result->businessDays[0]->unitGroups[0]->metrics->soldCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
    }

    public function testCompanyInvoicesVatMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'companyInvoices' => [[
                'company' => [
                    'id' => 'C1', 'code' => 'ACME', 'name' => 'Acme Corp', 'taxId' => 'DE123',
                    'address' => ['addressLine1' => 'Main St 1', 'postalCode' => '80331', 'city' => 'Munich', 'countryCode' => 'DE'],
                ],
                'invoice' => [
                    'number' => 'INV-1', 'date' => '2026-09-01',
                    'subTotal' => ['amount' => 119.0, 'currency' => 'EUR'],
                    'taxDetails' => [['vatType' => 'Normal', 'vatPercent' => 19.0, 'net' => ['amount' => 100.0, 'currency' => 'EUR'], 'tax' => ['amount' => 19.0, 'currency' => 'EUR']]],
                ],
            ]],
        ])));

        $result = $this->reports->companyInvoicesVat('MUC', dateFilter: ['gte_2026-09-01', 'lt_2026-10-01']);

        self::assertCount(1, $result);
        self::assertSame('Acme Corp', $result->items[0]->company->name);
        self::assertSame('Munich', $result->items[0]->company->address->city);
        self::assertSame(19.0, $result->items[0]->invoice->taxDetails[0]->vatPercent);

        $request = $this->lastRequest();
        self::assertStringContainsString('dateFilter=gte_2026-09-01%2Clt_2026-10-01', (string) $request->getUri());
    }

    public function testRevenuesMapsRecursiveTree(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'account' => ['name' => 'Total Revenue', 'number' => '0', 'type' => 'Revenues'],
            'netAmount' => ['amount' => 1000.0, 'currency' => 'EUR'],
            'grossAmount' => ['amount' => 1190.0, 'currency' => 'EUR'],
            'children' => [[
                'account' => ['name' => 'Accommodation', 'number' => '1', 'parentNumber' => '0', 'type' => 'Revenues'],
                'netAmount' => ['amount' => 1000.0, 'currency' => 'EUR'],
                'grossAmount' => ['amount' => 1190.0, 'currency' => 'EUR'],
                'children' => [],
            ]],
        ])));

        $result = $this->reports->revenues('MUC', new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2027-01-01'));

        self::assertSame('Total Revenue', $result->account->name);
        self::assertCount(1, $result->children);
        self::assertSame('Accommodation', $result->children[0]->account->name);
        self::assertSame([], $result->children[0]->children);
    }
}
