<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Offer;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Offer\OfferResource;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class OfferResourceTest extends TestCase
{
    private MockClient $httpClient;

    private OfferResource $offers;

    /** @var array<string, mixed> */
    private array $fullOfferFixture;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        $tokenProvider = new class implements TokenProvider {
            public function getToken(bool $forceRefresh = false): AccessToken
            {
                return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
            }
        };

        $pipeline = new RequestPipeline($this->httpClient, $factory, $factory, $tokenProvider);
        $this->offers = new OfferResource($pipeline);

        $this->fullOfferFixture = [
            'arrival' => '2026-09-20T17:00:00+02:00',
            'departure' => '2026-09-22T11:00:00+02:00',
            'unitGroup' => ['id' => 'MUC-SGL', 'code' => 'SGL', 'name' => 'Standard', 'description' => 'Standard', 'maxPersons' => 1, 'rank' => 2, 'type' => 'BedRoom'],
            'minGuaranteeType' => 'PM6Hold',
            'availableUnits' => 4,
            'ratePlan' => ['id' => 'MUC-NONREF_SGL', 'isSubjectToCityTax' => false],
            'totalGrossAmount' => ['amount' => 279.0, 'currency' => 'EUR'],
            'cancellationFee' => [
                'code' => 'FLEX', 'name' => 'Flexible', 'description' => 'Free cancellation.',
                'dueDateTime' => '2026-09-20T17:00:00+02:00', 'fee' => ['amount' => 279.0, 'currency' => 'EUR'],
            ],
            'noShowFee' => [
                'code' => 'NOSHOW', 'name' => 'Non Refundable', 'description' => 'No free no-show',
                'fee' => ['amount' => 279.0, 'currency' => 'EUR'],
            ],
            'timeSlices' => [[
                'from' => '2026-09-20T17:00:00+02:00', 'to' => '2026-09-21T11:00:00+02:00', 'availableUnits' => 4,
                'baseAmount' => ['grossAmount' => 107.0, 'netAmount' => 100.0, 'vatType' => 'Reduced', 'vatPercent' => 7.0, 'currency' => 'EUR'],
                'totalGrossAmount' => ['amount' => 117.0, 'currency' => 'EUR'],
            ]],
            'taxDetails' => [],
            'isCorporate' => false,
            'prePaymentAmount' => ['amount' => 0.0, 'currency' => 'EUR'],
        ];
    }

    public function testForPropertyReturnsStayOffers(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'property' => ['id' => 'MUC'],
            'offers' => [$this->fullOfferFixture],
        ])));

        $stayOffers = $this->offers->forProperty('MUC', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), 1);

        self::assertSame('MUC', $stayOffers->property?->id);
        self::assertCount(1, $stayOffers->offers);
        self::assertSame(279.0, $stayOffers->offers[0]->totalGrossAmount->amount);
        self::assertSame('MUC-SGL', $stayOffers->offers[0]->unitGroup->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('propertyId=MUC', (string) $request->getUri());
        self::assertStringContainsString('adults=1', (string) $request->getUri());
    }

    public function testForRatePlanReturnsStayOffers(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'property' => ['id' => 'MUC'],
            'offers' => [$this->fullOfferFixture],
        ])));

        $stayOffers = $this->offers->forRatePlan('MUC-NONREF_SGL', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), 1, overridePrices: [99.5]);

        self::assertCount(1, $stayOffers->offers);

        $request = $this->lastRequest();
        self::assertStringContainsString('ratePlanId=MUC-NONREF_SGL', (string) $request->getUri());
        self::assertStringContainsString('overridePrices=99.5', (string) $request->getUri());
    }

    public function testForRatePlanHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $stayOffers = $this->offers->forRatePlan('MUC-NONREF_SGL', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), 1);

        self::assertNull($stayOffers->property);
        self::assertSame([], $stayOffers->offers);
    }

    public function testServicesReturnsServiceOffers(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'services' => [[
                'service' => [
                    'id' => 'MUC-SPA', 'code' => 'SPA', 'name' => 'Spa access', 'description' => 'Spa access per stay',
                    'pricingUnit' => 'Person', 'defaultGrossPrice' => ['amount' => 30.0, 'currency' => 'EUR'],
                ],
                'count' => 1,
                'totalAmount' => ['grossAmount' => 30.0, 'netAmount' => 25.21, 'vatType' => 'Normal', 'vatPercent' => 19.0, 'currency' => 'EUR'],
                'prePaymentAmount' => ['amount' => 0.0, 'currency' => 'EUR'],
                'dates' => [[
                    'serviceDate' => '2026-09-20',
                    'amount' => ['grossAmount' => 30.0, 'netAmount' => 25.21, 'vatType' => 'Normal', 'vatPercent' => 19.0, 'currency' => 'EUR'],
                    'isDefaultDate' => true, 'isMandatory' => false,
                ]],
            ]],
        ])));

        $serviceOffers = $this->offers->services('MUC-NONREF_SGL', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), 1, channelCode: ChannelCode::Direct);

        self::assertCount(1, $serviceOffers->services);
        self::assertSame('MUC-SPA', $serviceOffers->services[0]->service->id);

        self::assertStringContainsString('channelCode=Direct', (string) $this->lastRequest()->getUri());
    }

    public function testIndexReturnsTimeSlices(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'timeSlices' => [[
                'from' => '2026-09-20T17:00:00+02:00',
                'to' => '2026-09-21T11:00:00+02:00',
                'offers' => [[
                    'unitGroup' => ['id' => 'MUC-SGL'],
                    'available' => 4,
                    'availableUnits' => 4,
                    'prices' => [[
                        'adults' => 1,
                        'price' => [
                            'beforeTax' => 100.0, 'afterTax' => 107.0, 'currency' => 'EUR',
                            'taxes' => ['tax' => 7.0, 'cityTax' => 0.0],
                        ],
                    ]],
                ]],
            ]],
        ])));

        $timeSlices = $this->offers->index('MUC-NONREF_SGL', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), ChannelCode::Direct);

        self::assertSame(1, $timeSlices->count);
        self::assertCount(1, $timeSlices->timeSlices);
        self::assertSame('MUC-SGL', $timeSlices->timeSlices[0]->offers[0]->unitGroup->id);
        self::assertSame(107.0, $timeSlices->timeSlices[0]->offers[0]->prices[0]->price->afterTax);

        $request = $this->lastRequest();
        self::assertStringContainsString('ratePlanId=MUC-NONREF_SGL', (string) $request->getUri());
        self::assertStringContainsString('channelCode=Direct', (string) $request->getUri());
    }

    public function testIndexHandlesEmptyOffersList(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 0])));

        $timeSlices = $this->offers->index('MUC-NONREF_SGL', new \DateTimeImmutable('2026-09-20'), new \DateTimeImmutable('2026-09-22'), ChannelCode::Direct);

        self::assertSame(0, $timeSlices->count);
        self::assertSame([], $timeSlices->timeSlices);
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
