<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Reservation;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\BookReservationService;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\DesiredStayDetails;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\DesiredTimeSlice;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationFilter;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationResource;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ReservationResourceTest extends TestCase
{
    private MockClient $httpClient;

    private ReservationResource $reservations;

    /** @var array<string, mixed> */
    private array $fullReservationFixture;

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
        $this->reservations = new ReservationResource($pipeline);

        $this->fullReservationFixture = [
            'id' => 'XPGMSXGF-1',
            'bookingId' => 'XPGMSXGF',
            'status' => 'Confirmed',
            'property' => ['id' => 'MUC', 'code' => 'MUC', 'name' => 'Demo Hotel Munich'],
            'ratePlan' => ['id' => 'MUC-NONREF_SGL', 'code' => 'NONREF', 'name' => 'Non Refundable', 'isSubjectToCityTax' => false],
            'unitGroup' => ['id' => 'MUC-SGL', 'code' => 'SGL', 'name' => 'Single', 'type' => 'BedRoom'],
            'unit' => ['id' => 'MUC-MTA', 'name' => 'A.101', 'unitGroupId' => 'MUC-SGL'],
            'totalGrossAmount' => ['amount' => 110.0, 'currency' => 'EUR'],
            'arrival' => '2026-09-20T17:00:00+02:00',
            'departure' => '2026-09-22T11:00:00+02:00',
            'created' => '2026-09-18T11:31:09+02:00',
            'modified' => '2026-09-18T11:31:09+02:00',
            'adults' => 1,
            'channelCode' => 'Direct',
            'primaryGuest' => ['lastName' => 'Doe', 'firstName' => 'John'],
            'guaranteeType' => 'CreditCard',
            'cancellationFee' => [
                'id' => 'cf1', 'code' => 'CF1', 'name' => 'Standard', 'description' => 'Standard fee',
                'dueDateTime' => '2026-09-19T17:00:00+02:00', 'fee' => ['amount' => 20.0, 'currency' => 'EUR'],
            ],
            'noShowFee' => [
                'id' => 'ns1', 'code' => 'NS1', 'name' => 'Standard', 'description' => 'Standard fee',
                'fee' => ['amount' => 110.0, 'currency' => 'EUR'],
            ],
            'balance' => ['amount' => 0.0, 'currency' => 'EUR'],
            'allFoliosHaveInvoice' => false,
            'taxDetails' => [],
            'hasCityTax' => false,
            'payableAmount' => ['guest' => ['amount' => 110.0, 'currency' => 'EUR']],
            'isPreCheckedIn' => false,
            'isOpenForCharges' => true,
            'isUnitAssignmentLocked' => false,
            'hasActivePaymentAccount' => false,
        ];
    }

    public function testGetReservationMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullReservationFixture)));

        $reservation = $this->reservations->get('XPGMSXGF-1');

        self::assertSame('XPGMSXGF-1', $reservation->id);
        self::assertSame('XPGMSXGF', $reservation->bookingId);
        self::assertSame(ReservationStatus::Confirmed, $reservation->status);
        self::assertSame(ChannelCode::Direct, $reservation->channelCode);
        self::assertSame(GuaranteeType::CreditCard, $reservation->guaranteeType);
        self::assertSame('MUC', $reservation->property->id);
        self::assertSame('Doe', $reservation->primaryGuest?->lastName);
        self::assertSame(20.0, $reservation->cancellationFee?->fee->amount);
    }

    public function testUnknownStatusFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fullReservationFixture;
        $fixture['status'] = 'SomeFutureStatus';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $reservation = $this->reservations->get('XPGMSXGF-1');

        self::assertSame(ReservationStatus::Unknown, $reservation->status);
    }

    public function testNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found',
            'title' => 'Reservation not found',
            'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->reservations->get('MISSING');
    }

    public function testListMapsWrappedResponse(): void
    {
        $second = $this->fullReservationFixture;
        $second['id'] = 'XPGMSXGF-2';

        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 2,
            'reservations' => [$this->fullReservationFixture, $second],
        ])));

        $result = $this->reservations->list(new ReservationFilter(bookingId: 'XPGMSXGF'), pageSize: 50);

        self::assertCount(2, $result);
        self::assertSame(2, $result->totalCount);
        self::assertSame('XPGMSXGF-1', $result->items[0]->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('bookingId=XPGMSXGF', (string) $request->getUri());
        self::assertStringContainsString('pageSize=50', (string) $request->getUri());
    }

    public function testListHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->reservations->list();

        self::assertCount(0, $result);
    }

    public function testCountReturnsCountValue(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['count' => 7])));

        self::assertSame(7, $this->reservations->count(new ReservationFilter(status: [ReservationStatus::Confirmed])));

        $request = $this->lastRequest();
        self::assertStringContainsString('status=Confirmed', (string) $request->getUri());
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->update('XPGMSXGF-1', new JsonPatch()->replace('/comment', 'VIP guest'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/comment', 'value' => 'VIP guest']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testServicesReturnsList(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'services' => [[
                'service' => [
                    'id' => 'MUC-YOGA', 'code' => 'YOGA', 'name' => 'Sun Salutation', 'description' => 'Yoga session',
                    'pricingUnit' => 'Person', 'defaultGrossPrice' => ['amount' => 35.0, 'currency' => 'EUR'],
                ],
                'totalAmount' => ['grossAmount' => 30.0, 'netAmount' => 25.0, 'vatType' => 'Normal', 'vatPercent' => 19.0, 'currency' => 'EUR'],
                'dates' => [[
                    'serviceDate' => '2026-09-18', 'count' => 1, 'isMandatory' => false,
                    'amount' => ['grossAmount' => 30.0, 'netAmount' => 25.0, 'vatType' => 'Normal', 'vatPercent' => 19.0, 'currency' => 'EUR'],
                ]],
            ]],
        ])));

        $services = $this->reservations->services('XPGMSXGF-1');

        self::assertCount(1, $services);
        self::assertSame('MUC-YOGA', $services[0]->service->id);
        self::assertSame(30.0, $services[0]->totalAmount->grossAmount);
    }

    public function testRemoveServiceSendsDeleteRequestWithServiceIdQuery(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->removeService('XPGMSXGF-1', 'MUC-YOGA');

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertStringContainsString('serviceId=MUC-YOGA', (string) $request->getUri());
    }

    public function testBookServiceSendsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->bookService('XPGMSXGF-1', new BookReservationService('MUC-SPA', count: 2));

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/reservation-actions/XPGMSXGF-1/book-service', (string) $request->getUri());
        self::assertStringNotContainsString('$force', (string) $request->getUri());
        self::assertSame(['serviceId' => 'MUC-SPA', 'count' => 2], json_decode((string) $request->getBody(), true));
    }

    public function testBookServiceForceAppendsForceSegment(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->bookService('XPGMSXGF-1', new BookReservationService('MUC-SPA'), force: true);

        self::assertStringContainsString('/book-service/$force', (string) $this->lastRequest()->getUri());
    }

    public function testCheckInSendsPutRequestWithOptionalCityTaxQuery(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->checkIn('XPGMSXGF-1', withCityTax: true);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/reservation-actions/XPGMSXGF-1/checkin', (string) $request->getUri());
        self::assertStringContainsString('withCityTax=true', (string) $request->getUri());
    }

    #[DataProvider('provideSimpleActionSendsPutRequestCases')]
    public function testSimpleActionSendsPutRequest(string $method, string $expectedPathSegment): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->{$method}('XPGMSXGF-1');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/reservation-actions/XPGMSXGF-1/'.$expectedPathSegment, (string) $request->getUri());
    }

    /** @return iterable<string, list<string>> */
    public static function provideSimpleActionSendsPutRequestCases(): iterable
    {
        yield 'checkOut' => ['checkOut', 'checkout'];

        yield 'cancel' => ['cancel', 'cancel'];

        yield 'noShow' => ['noShow', 'noshow'];

        yield 'revertCheckIn' => ['revertCheckIn', 'revert-checkin'];

        yield 'addCityTax' => ['addCityTax', 'add-city-tax'];

        yield 'removeCityTax' => ['removeCityTax', 'remove-city-tax'];

        yield 'lockUnit' => ['lockUnit', 'lock-unit'];

        yield 'unlockUnit' => ['unlockUnit', 'unlock-unit'];

        yield 'unassignUnits' => ['unassignUnits', 'unassign-units'];
    }

    public function testAssignUnitReturnsAutoAssignedTimeSlices(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'timeSlices' => [[
                'unit' => ['id' => 'MUC-JQI', 'name' => 'A.102', 'unitGroupId' => 'MUC-DBL'],
                'from' => '2026-09-18T17:00:00+02:00',
                'to' => '2026-09-19T11:00:00+02:00',
            ]],
        ])));

        $items = $this->reservations->assignUnit('XPGMSXGF-1', unitConditions: ['Clean']);

        self::assertCount(1, $items);
        self::assertSame('MUC-JQI', $items[0]->unit->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('unitConditions=Clean', (string) $request->getUri());
    }

    public function testAssignSpecificUnitReturnsAssignedUnit(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'unit' => ['id' => 'MUC-JQI', 'name' => 'A.102', 'unitGroupId' => 'MUC-DBL'],
        ])));

        $unit = $this->reservations->assignSpecificUnit('XPGMSXGF-1', 'MUC-JQI', lockUnit: true);

        self::assertSame('MUC-JQI', $unit->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('/reservation-actions/XPGMSXGF-1/assign-unit/MUC-JQI', (string) $request->getUri());
        self::assertStringContainsString('lockUnit=true', (string) $request->getUri());
    }

    public function testAmendSendsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->amend('XPGMSXGF-1', new DesiredStayDetails(
            arrival: '2026-09-20T17:00:00+02:00',
            departure: '2026-09-23T11:00:00+02:00',
            adults: 2,
            timeSlices: [new DesiredTimeSlice('MUC-NONREF_SGL')],
        ));

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/reservation-actions/XPGMSXGF-1/amend', (string) $request->getUri());
        self::assertStringNotContainsString('$force', (string) $request->getUri());

        /** @var array{adults: int, timeSlices: list<array{ratePlanId: string}>} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame(2, $body['adults']);
        self::assertSame('MUC-NONREF_SGL', $body['timeSlices'][0]['ratePlanId']);
    }

    public function testAmendForceAppendsForceSegment(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->reservations->amend('XPGMSXGF-1', new DesiredStayDetails(
            arrival: '2026-09-20T17:00:00+02:00',
            departure: '2026-09-23T11:00:00+02:00',
            adults: 2,
            timeSlices: [new DesiredTimeSlice('MUC-NONREF_SGL')],
        ), force: true);

        self::assertStringContainsString('/amend/$force', (string) $this->lastRequest()->getUri());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
