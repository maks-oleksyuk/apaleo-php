<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\PersonAddress;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\BulkAllowanceItem;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\BulkMoveItem;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateCharge;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateFolio;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateFolioAllowance;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\CreateTransitoryCharge;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\FolioDebitor;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\FolioItemSelection;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\Split;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\ChargeType;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioAction;
use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioStatus;
use Oleksyuk\Apaleo\Resource\Finance\Folio\FolioFilter;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\DebitorType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Reports\Enum\Title;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Finance')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class FolioResourceTest extends FinanceTestCase
{
    public function testGetFolioMapsFullModel(): void
    {
        $this->respond($this->folioFixture() + [
            'property' => ['id' => 'MUC'],
            'pendingPayments' => [['id' => 'P9', 'amount' => ['amount' => 20, 'currency' => 'EUR'], 'terminalId' => 'T1']],
            'relatedFolios' => [['id' => 'F2', 'debitor' => 'Jane Doe']],
            'allowedPayment' => 80,
        ]);

        $folio = $this->api->folios()->get('F1', ['folios']);

        self::assertSame(FolioType::Guest, $folio->type);
        self::assertSame(FolioStatus::Open, $folio->status);
        self::assertSame('MUC', $folio->property->id);
        self::assertSame('R1', $folio->reservationId);
        self::assertSame(DebitorType::PrimaryGuest, $folio->debitor?->type);
        self::assertSame(Title::Mr, $folio->debitor->title);
        self::assertSame('Munich', $folio->debitor->address?->city);
        self::assertSame(ChargeType::TimeSlice, $folio->charges[0]->type);
        self::assertSame(FinanceServiceType::Accommodation, $folio->charges[0]->serviceType);
        self::assertSame(VatType::Reduced, $folio->charges[0]->amount->vatType);
        self::assertSame('F0', $folio->charges[0]->movedFrom?->id);
        self::assertSame(['de' => 'Übernachtung'], $folio->charges[0]->translatedNames);
        self::assertSame(PaymentMethod::Cash, $folio->payments[0]->method);
        self::assertSame('T1', $folio->pendingPayments[0]->terminalId);
        self::assertSame('Jane Doe', $folio->relatedFolios[0]->debitor);
        self::assertSame(['I1'], $folio->relatedInvoiceIds);
        self::assertSame([FolioAction::AddCharge, FolioAction::Close], $folio->allowedActions);
        self::assertSame(80.0, $folio->allowedPayment);
        self::assertNull($folio->maximumAllowance);
        self::assertStringEndsWith('/finance/v1/folios/F1?expand=folios', $this->lastUri());
    }

    public function testListFoliosSendsFilterAndMapsItems(): void
    {
        $this->respond(['count' => 7, 'folios' => [$this->folioFixture()]]);

        $result = $this->api->folios()->list(
            new FolioFilter(propertyIds: ['MUC'], type: FolioType::Guest, excludeClosed: true, createdFrom: new \DateTimeImmutable('2026-01-01T00:00:00+00:00')),
            pageSize: 10,
            sort: ['created:desc'],
            expand: ['charges', 'payments'],
        );

        self::assertSame(7, $result->totalCount);
        self::assertSame(-100.0, $result[0]->balance->amount);
        self::assertStringContainsString('propertyIds=MUC&type=Guest&excludeClosed=true&createdFrom=2026-01-01T00:00:00+00:00&pageSize=10&sort=created:desc&expand=charges,payments', $this->lastUri());
    }

    public function testCreateFolioSendsDebitorAndIdempotencyKey(): void
    {
        $this->respond(['id' => 'F9'], 201);

        $id = $this->api->folios()->create(new CreateFolio(
            debitor: new FolioDebitor(type: DebitorType::Company, name: 'Acme', address: new PersonAddress(null, null, null, 'Berlin', null, 'DE')),
            type: FolioType::External,
            propertyId: 'MUC',
        ), 'key-1');

        self::assertSame('F9', $id);
        self::assertSame('key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));
        self::assertSame([
            'debitor' => ['type' => 'Company', 'name' => 'Acme', 'address' => ['city' => 'Berlin', 'countryCode' => 'DE']],
            'type' => 'External',
            'propertyId' => 'MUC',
        ], $this->lastBody());
    }

    public function testChargeAndAllowanceActions(): void
    {
        $this->respond(['id' => 'C9', 'feeChargeIds' => ['C10']]);
        $charge = $this->api->folios()->addCharge('F1', new CreateCharge('Minibar', new MonetaryValue(12.5, 'EUR'), FinanceServiceType::FoodAndBeverages, VatType::Normal, quantity: 2));

        self::assertSame(['C10'], $charge->feeChargeIds);
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/charges', $this->lastUri());
        self::assertSame(['name' => 'Minibar', 'amount' => ['amount' => 12.5, 'currency' => 'EUR'], 'serviceType' => 'FoodAndBeverages', 'vatType' => 'Normal', 'quantity' => 2], $this->lastBody());

        $this->respond(['id' => 'C11']);
        $this->api->folios()->addNoShowFee('F1', new MonetaryValue(50, 'EUR'));
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/no-show-fee', $this->lastUri());
        self::assertSame(['amount' => 50, 'currency' => 'EUR'], $this->lastBody());

        $this->respond(['items' => [['id' => 'A1', 'sourceChargeId' => 'C1'], ['id' => 'A2', 'sourceChargeId' => 'C2']]]);
        $allowances = $this->api->folios()->addBulkAllowances('F1', [new BulkAllowanceItem('C1', new MonetaryValue(5, 'EUR'))], 'Complaint', new \DateTimeImmutable('2026-03-01'));
        self::assertSame(['C1' => 'A1', 'C2' => 'A2'], $allowances);
        self::assertSame(['items' => [['chargeId' => 'C1', 'amount' => ['amount' => 5, 'currency' => 'EUR']]], 'reason' => 'Complaint', 'businessDate' => '2026-03-01'], $this->lastBody());
    }

    public function testMoveCorrectSplitAndSimpleActions(): void
    {
        $this->respond([], 204);
        $this->api->folios()->moveCharges('F1', 'F2', 'Wrong folio', new FolioItemSelection(chargeIds: ['C1'], transitoryChargeIds: ['T1']));
        self::assertSame('PUT', $this->lastRequest()->getMethod());
        self::assertSame(['targetFolioId' => 'F2', 'reason' => 'Wrong folio', 'chargeIds' => ['C1'], 'transitoryChargeIds' => ['T1']], $this->lastBody());

        $this->respond(['id' => 'F3'], 201);
        self::assertSame('F3', $this->api->folios()->correct('F1', 'Split bill', new FolioItemSelection(allowanceIds: ['A1'])));
        self::assertSame(['reason' => 'Split bill', 'allowanceIds' => ['A1']], $this->lastBody());

        $this->respond(['allowanceId' => 'A5', 'firstChargeId' => 'C5', 'secondChargeId' => 'C6']);
        $split = $this->api->folios()->splitCharge('F1', 'C1', Split::byPercent(30));
        self::assertSame('C6', $split->secondChargeId);
        self::assertSame(['type' => 'ByPercent', 'percent' => 30], $this->lastBody());
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/charges/C1/split', $this->lastUri());

        $this->respond([], 204);
        $this->api->folios()->close('F1');
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/close', $this->lastUri());
        self::assertSame('PUT', $this->lastRequest()->getMethod());
    }

    public function testFeesAllowancesAndTransitoryCharges(): void
    {
        $this->respond(['id' => 'C20', 'feeChargeIds' => []]);
        $fee = $this->api->folios()->addCancellationFee('F1', new MonetaryValue(80, 'EUR'), 'key-1');
        self::assertSame('C20', $fee->id);
        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/cancellation-fee', $this->lastUri());
        self::assertSame('key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));
        self::assertSame(['amount' => 80, 'currency' => 'EUR'], $this->lastBody());

        $this->respond(['id' => 'T1']);
        self::assertSame('T1', $this->api->folios()->addTransitoryCharge('F1', new CreateTransitoryCharge('Theatre', new MonetaryValue(40, 'EUR'), FinanceServiceType::Other, quantity: 2, businessDate: new \DateTimeImmutable('2026-03-01'))));
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/transitory-charges', $this->lastUri());
        self::assertSame(['name' => 'Theatre', 'amount' => ['amount' => 40, 'currency' => 'EUR'], 'serviceType' => 'Other', 'quantity' => 2, 'businessDate' => '2026-03-01'], $this->lastBody());

        $this->respond(['id' => 'A7']);
        self::assertSame('A7', $this->api->folios()->addChargeAllowance('F1', 'C1', 'Noise', new MonetaryValue(10, 'EUR')));
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/charges/C1/allowances', $this->lastUri());
        self::assertSame(['reason' => 'Noise', 'amount' => ['amount' => 10, 'currency' => 'EUR']], $this->lastBody());

        $this->respond(['id' => 'A8']);
        self::assertSame('A8', $this->api->folios()->addFolioAllowance('F1', new CreateFolioAllowance('Goodwill', new MonetaryValue(15, 'EUR'), FinanceServiceType::Accommodation, VatType::Reduced)));
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/allowances', $this->lastUri());
        self::assertSame(['reason' => 'Goodwill', 'amount' => ['amount' => 15, 'currency' => 'EUR'], 'serviceType' => 'Accommodation', 'vatType' => 'Reduced'], $this->lastBody());
    }

    public function testMovesAndReopenPostChargesActions(): void
    {
        $this->respond([], 204);
        $this->api->folios()->moveAllCharges('F1', 'F2', 'Merge');
        self::assertSame('PUT', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/move-all-charges', $this->lastUri());
        self::assertSame(['targetFolioId' => 'F2', 'reason' => 'Merge'], $this->lastBody());

        $this->respond([], 204);
        $this->api->folios()->bulkMoveCharges([new BulkMoveItem('F1', 'F2', ['C1']), new BulkMoveItem('F3', 'F2')], 'Group bill');
        self::assertStringEndsWith('/finance/v1/folio-actions/bulk-move', $this->lastUri());
        self::assertSame(['items' => [['sourceFolioId' => 'F1', 'targetFolioId' => 'F2', 'chargeIds' => ['C1']], ['sourceFolioId' => 'F3', 'targetFolioId' => 'F2']], 'reason' => 'Group bill'], $this->lastBody());

        $this->respond([], 204);
        $this->api->folios()->movePayments('F1', 'F2', 'Wrong guest', ['P1']);
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/move-payments', $this->lastUri());
        self::assertSame(['targetFolioId' => 'F2', 'reason' => 'Wrong guest', 'paymentIds' => ['P1']], $this->lastBody());

        foreach (['reopen' => 'reopen', 'postCharges' => 'post-charges'] as $method => $action) {
            $this->respond([], 204);
            $this->api->folios()->{$method}('F1');
            self::assertSame('PUT', $this->lastRequest()->getMethod());
            self::assertStringEndsWith("/finance/v1/folio-actions/F1/{$action}", $this->lastUri());
            self::assertSame('', (string) $this->lastRequest()->getBody());
        }
    }

    /** @return array<string, mixed> */
    private function folioFixture(): array
    {
        return [
            'id' => 'F1',
            'created' => '2026-03-01T10:00:00+01:00',
            'updated' => '2026-03-02T10:00:00+01:00',
            'type' => 'Guest',
            'status' => 'Open',
            'debitor' => ['type' => 'PrimaryGuest', 'title' => 'Mr', 'firstName' => 'John', 'name' => 'Doe', 'address' => ['city' => 'Munich', 'countryCode' => 'DE']],
            'reservation' => ['id' => 'R1', 'bookingId' => 'B1'],
            'bookingId' => 'B1',
            'balance' => ['amount' => -100, 'currency' => 'EUR'],
            'isMainFolio' => true,
            'charges' => [[
                'id' => 'C1', 'type' => 'TimeSlice', 'serviceType' => 'Accommodation', 'name' => 'Night', 'translatedNames' => ['de' => 'Übernachtung'],
                'isPosted' => true, 'serviceDate' => '2026-03-01', 'created' => '2026-03-01T10:00:00+01:00', 'quantity' => 1,
                'amount' => ['grossAmount' => 107, 'netAmount' => 100, 'vatType' => 'Reduced', 'vatPercent' => 7, 'currency' => 'EUR'],
                'movedFrom' => ['id' => 'F0'],
            ]],
            'payments' => [['id' => 'P1', 'method' => 'Cash', 'amount' => ['amount' => 207, 'currency' => 'EUR'], 'paymentDate' => '2026-03-01T12:00:00+01:00', 'businessDate' => '2026-03-01']],
            'relatedInvoices' => [['id' => 'I1']],
            'allowedActions' => ['AddCharge', 'Close'],
        ];
    }
}
