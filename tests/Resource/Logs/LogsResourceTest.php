<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Logs;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Resource\Logs\Enum\NightAuditFailureCode;
use Oleksyuk\Apaleo\Resource\Logs\Enum\NightAuditStatus;
use Oleksyuk\Apaleo\Resource\Logs\Enum\ReservationChangeType;
use Oleksyuk\Apaleo\Resource\Logs\Enum\ReservationLogEventType;
use Oleksyuk\Apaleo\Resource\Logs\LogsResource;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Logs')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class LogsResourceTest extends TestCase
{
    use MockPipeline;

    private LogsResource $logs;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->logs = new LogsResource($pipeline);
    }

    public function testReservationChangesMapsAddedChange(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'logEntries' => [[
                'reservationId' => 'XPGMSXGF-1',
                'eventType' => 'Created',
                'clientId' => 'ibe',
                'propertyId' => 'MUC',
                'created' => '2026-09-20T10:00:00+02:00',
                'subjectId' => 'user-1',
                'changes' => [[
                    'changeType' => 'ReservationAdded',
                    'reservationAddedValue' => [
                        'arrival' => '2026-09-20T14:00:00+02:00',
                        'departure' => '2026-09-22T11:00:00+02:00',
                        'status' => 'Confirmed',
                        'totalGrossAmount' => ['amount' => 200.0, 'currency' => 'EUR'],
                        'adults' => 2,
                        'channelCode' => 'Direct',
                        'primaryGuest' => ['lastName' => 'Doe', 'firstName' => 'John', 'gender' => 'Male'],
                        'childrenAges' => [4, 7],
                        'additionalGuests' => [],
                        'timeSlices' => [['ratePlanId' => 'MUC-SGL-STD', 'from' => '2026-09-20T00:00:00+02:00', 'to' => '2026-09-22T00:00:00+02:00']],
                        'extraServices' => [],
                        'guaranteeType' => 'CreditCard',
                        'cancellationFee' => [],
                        'noShowFee' => ['amount' => 0.0, 'currency' => 'EUR'],
                        'hasCityTax' => true,
                        'validationMessages' => [],
                    ],
                ]],
            ]],
        ])));

        $result = $this->logs->reservationChanges(expandChanges: true);

        self::assertCount(1, $result);
        $entry = $result->items[0];
        self::assertSame('XPGMSXGF-1', $entry->reservationId);
        self::assertSame(ReservationLogEventType::Created, $entry->eventType);
        self::assertCount(1, $entry->changes);

        $change = $entry->changes[0];
        self::assertSame(ReservationChangeType::ReservationAdded, $change->changeType);
        self::assertNotNull($change->reservationAddedValue);
        self::assertSame(2, $change->reservationAddedValue->adults);
        self::assertNotNull($change->reservationAddedValue->primaryGuest);
        self::assertSame('Doe', $change->reservationAddedValue->primaryGuest->lastName);
        self::assertSame([4, 7], $change->reservationAddedValue->childrenAges);
        self::assertCount(1, $change->reservationAddedValue->timeSlices);
        self::assertSame('MUC-SGL-STD', $change->reservationAddedValue->timeSlices[0]->ratePlanId);

        $request = $this->lastRequest();
        self::assertStringContainsString('expand=changes', (string) $request->getUri());
    }

    public function testReservationChangesMapsChangedDiff(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'logEntries' => [[
                'reservationId' => 'XPGMSXGF-1',
                'eventType' => 'Changed',
                'clientId' => 'ibe',
                'propertyId' => 'MUC',
                'created' => '2026-09-20T10:00:00+02:00',
                'changes' => [[
                    'changeType' => 'ReservationChanged',
                    'oldReservationChangedValue' => ['adults' => 1],
                    'newReservationChangedValue' => ['adults' => 2],
                ]],
            ]],
        ])));

        $result = $this->logs->reservationChanges(expandChanges: true);

        $change = $result->items[0]->changes[0];
        self::assertSame(1, $change->oldReservationChangedValue?->adults);
        self::assertSame(2, $change->newReservationChangedValue?->adults);
    }

    public function testReservationChangesHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->logs->reservationChanges();

        self::assertCount(0, $result);
    }

    public function testFolioChangesMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'logEntries' => [[
                'folioId' => 'FOL-1',
                'eventType' => 'ChargePosted',
                'amount' => ['amount' => 50.0, 'currency' => 'EUR'],
                'clientId' => 'pms',
                'propertyId' => 'MUC',
                'created' => '2026-09-20T10:00:00+02:00',
            ]],
        ])));

        $result = $this->logs->folioChanges(folioIds: ['FOL-1']);

        self::assertCount(1, $result);
        self::assertSame('FOL-1', $result->items[0]->folioId);
        self::assertSame(50.0, $result->items[0]->amount?->amount);

        $request = $this->lastRequest();
        self::assertStringContainsString('folioIds=FOL-1', (string) $request->getUri());
    }

    public function testNightAuditMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'logEntries' => [[
                'ended' => '2026-09-20T04:00:00+02:00',
                'setReservationsToNoShow' => true,
                'status' => 'Failure',
                'failureCode' => 'ProcessFoliosFailed',
                'reservationIdsSetToNoShow' => ['R1'],
                'propertyId' => 'MUC',
                'created' => '2026-09-20T04:05:00+02:00',
            ]],
        ])));

        $result = $this->logs->nightAudit(statuses: [NightAuditStatus::Failure]);

        self::assertCount(1, $result);
        self::assertSame(NightAuditStatus::Failure, $result->items[0]->status);
        self::assertSame(NightAuditFailureCode::ProcessFoliosFailed, $result->items[0]->failureCode);
        self::assertSame(['R1'], $result->items[0]->reservationIdsSetToNoShow);
    }

    public function testTransactionsExportMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'logEntries' => [[
                'periodStart' => '2026-09-01T00:00:00+02:00',
                'periodEnd' => '2026-09-30T00:00:00+02:00',
                'type' => 'Aggregate',
                'propertyId' => 'MUC',
                'created' => '2026-10-01T02:00:00+02:00',
            ]],
        ])));

        $result = $this->logs->transactionsExport();

        self::assertCount(1, $result);
        self::assertSame('MUC', $result->items[0]->propertyId);
    }
}
