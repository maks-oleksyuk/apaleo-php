<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Booking;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Booking\BookingDomainResource;
use Oleksyuk\Apaleo\Resource\Booking\Booking\BookingFilter;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\CreateBooking;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\CreateReservation;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\CreateReservationTimeSlice;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class BookingDomainResourceTest extends TestCase
{
    private MockClient $httpClient;

    private BookingDomainResource $bookings;

    /** @var array<string, mixed> */
    private array $fullBookingFixture;

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
        $this->bookings = new BookingDomainResource($pipeline);

        $this->fullBookingFixture = [
            'id' => 'XPGMSXGF',
            'booker' => ['lastName' => 'Doe', 'firstName' => 'John'],
            'hasActivePaymentAccount' => false,
            'comment' => 'Front desk note',
            'created' => '2026-09-18T11:31:09+02:00',
            'modified' => '2026-09-18T11:31:09+02:00',
            'reservations' => [[
                'id' => 'XPGMSXGF-1',
                'status' => 'Confirmed',
                'channelCode' => 'Direct',
                'hasActivePaymentAccount' => false,
                'arrival' => '2026-09-20T17:00:00+02:00',
                'departure' => '2026-09-22T11:00:00+02:00',
                'adults' => 1,
                'totalGrossAmount' => ['amount' => 110.0, 'currency' => 'EUR'],
                'property' => ['id' => 'MUC'],
                'ratePlan' => ['id' => 'MUC-NONREF_SGL', 'isSubjectToCityTax' => false],
                'unitGroup' => ['id' => 'MUC-SGL'],
                'cancellationFee' => [
                    'id' => 'cf1', 'code' => 'CF1', 'name' => 'Standard', 'description' => 'Standard fee',
                    'dueDateTime' => '2026-09-19T17:00:00+02:00', 'fee' => ['amount' => 20.0, 'currency' => 'EUR'],
                ],
                'noShowFee' => [
                    'id' => 'ns1', 'code' => 'NS1', 'name' => 'Standard', 'description' => 'Standard fee',
                    'fee' => ['amount' => 110.0, 'currency' => 'EUR'],
                ],
                'isPreCheckedIn' => false,
                'isOpenForCharges' => true,
            ]],
        ];
    }

    public function testGetBookingMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullBookingFixture)));

        $booking = $this->bookings->get('XPGMSXGF');

        self::assertSame('XPGMSXGF', $booking->id);
        self::assertSame('Doe', $booking->booker?->lastName);
        self::assertCount(1, $booking->reservations);
        self::assertSame('XPGMSXGF-1', $booking->reservations[0]->id);
        self::assertSame(20.0, $booking->reservations[0]->cancellationFee->fee->amount);
    }

    public function testNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found', 'title' => 'Booking not found', 'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->bookings->get('MISSING');
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'bookings' => [$this->fullBookingFixture],
        ])));

        $result = $this->bookings->list(new BookingFilter(groupId: 'GRP1'), pageSize: 25);

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);
        self::assertSame('XPGMSXGF', $result->items[0]->id);

        $request = $this->lastRequest();
        self::assertStringContainsString('groupId=GRP1', (string) $request->getUri());
        self::assertStringContainsString('pageSize=25', (string) $request->getUri());
    }

    public function testCreateReturnsBookingAndReservationIds(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode([
            'id' => 'XPGMSXGF',
            'reservationIds' => [['id' => 'XPGMSXGF-1'], ['id' => 'XPGMSXGF-2']],
        ])));

        $created = $this->bookings->create(new CreateBooking(
            booker: new Booker('Doe', firstName: 'John'),
            reservations: [new CreateReservation(
                arrival: '2026-09-20',
                departure: '2026-09-22',
                adults: 1,
                channelCode: ChannelCode::Direct,
                timeSlices: [new CreateReservationTimeSlice('MUC-NONREF_SGL')],
            )],
        ), idempotencyKey: 'idem-1');

        self::assertSame('XPGMSXGF', $created->id);
        self::assertSame(['XPGMSXGF-1', 'XPGMSXGF-2'], $created->reservationIds);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringNotContainsString('$force', (string) $request->getUri());
        self::assertSame('idem-1', $request->getHeaderLine('Idempotency-Key'));

        /** @var array{booker: array{lastName: string}} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('Doe', $body['booker']['lastName']);
    }

    public function testCreateForceAppendsForceSegment(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode([
            'id' => 'XPGMSXGF', 'reservationIds' => [],
        ])));

        $this->bookings->create(new CreateBooking(
            booker: new Booker('Doe'),
            reservations: [new CreateReservation(
                arrival: '2026-09-20',
                departure: '2026-09-22',
                adults: 1,
                channelCode: ChannelCode::Direct,
                timeSlices: [new CreateReservationTimeSlice('MUC-NONREF_SGL')],
            )],
        ), force: true);

        self::assertStringContainsString('/bookings/$force', (string) $this->lastRequest()->getUri());
    }

    public function testUpdateSendsPatch(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->bookings->update('XPGMSXGF', new JsonPatch()->replace('/comment', 'VIP'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            [['op' => 'replace', 'path' => '/comment', 'value' => 'VIP']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testAddReservationsReturnsCreatedIds(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode([
            'reservationIds' => [['id' => 'XPGMSXGF-3']],
        ])));

        $created = $this->bookings->addReservations('XPGMSXGF', [new CreateReservation(
            arrival: '2026-09-24',
            departure: '2026-09-25',
            adults: 1,
            channelCode: ChannelCode::Direct,
            timeSlices: [new CreateReservationTimeSlice('MUC-NONREF_SGL')],
        )]);

        self::assertSame(['XPGMSXGF-3'], $created->reservationIds);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringContainsString('/bookings/XPGMSXGF/reservations', (string) $request->getUri());
        self::assertStringNotContainsString('$force', (string) $request->getUri());
    }

    public function testAddReservationsForceAppendsForceSegment(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['reservationIds' => []])));

        $this->bookings->addReservations('XPGMSXGF', [new CreateReservation(
            arrival: '2026-09-24',
            departure: '2026-09-25',
            adults: 1,
            channelCode: ChannelCode::Direct,
            timeSlices: [new CreateReservationTimeSlice('MUC-NONREF_SGL')],
        )], force: true);

        self::assertStringContainsString('/bookings/XPGMSXGF/reservations/$force', (string) $this->lastRequest()->getUri());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
