<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\TransactionCommand;
use Oleksyuk\Apaleo\Resource\Finance\Account\TransactionFilter;
use Oleksyuk\Apaleo\Resource\Finance\Routing\DTO\CreateRouting;
use Oleksyuk\Apaleo\Resource\Finance\Routing\DTO\CreateRoutingChargeFilter;
use Oleksyuk\Apaleo\Resource\Finance\Routing\RoutingFilter;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\DebitorType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;

/**
 * @internal
 *
 * @coversNothing
 */
final class RoutingAccountAndTypesTest extends FinanceTestCase
{
    public function testRoutingListAndCreate(): void
    {
        $this->respond(['count' => 1, 'routings' => [[
            'id' => 'RT1', 'bookingId' => 'B1', 'propertyId' => 'MUC',
            'destinationFolio' => ['id' => 'F2', 'debitorType' => 'Company'],
            'filter' => ['serviceTypes' => ['FoodAndBeverages'], 'subAccounts' => [['id' => 'MUC-ALCO', 'name' => 'Alcohol']], 'from' => '2026-03-01'],
        ]]]);

        $routings = $this->api->routings()->list(new RoutingFilter(bookingIds: ['B1']));

        self::assertSame('F2', $routings[0]->destinationFolioId);
        self::assertSame(DebitorType::Company, $routings[0]->destinationDebitorType);
        self::assertSame([FinanceServiceType::FoodAndBeverages], $routings[0]->filter?->serviceTypes);
        self::assertSame(['MUC-ALCO' => 'Alcohol'], $routings[0]->filter->subAccounts);
        self::assertStringEndsWith('/finance/v1/routings?bookingIds=B1', $this->lastUri());

        $this->respond(['id' => 'RT2'], 201);
        $this->api->routings()->create(new CreateRouting('B1', 'MUC', 'F2', new CreateRoutingChargeFilter(serviceTypes: [FinanceServiceType::Accommodation], to: new \DateTimeImmutable('2026-03-05'))));

        self::assertSame(['bookingId' => 'B1', 'propertyId' => 'MUC', 'destinationFolioId' => 'F2', 'filter' => ['serviceTypes' => ['Accommodation'], 'to' => '2026-03-05']], $this->lastBody());
    }

    public function testExportDailyUsesBusinessDaysAndReference(): void
    {
        $this->respond(['transactions' => [[
            'timestamp' => '2026-03-01T10:00:00Z', 'date' => '2026-03-01', 'command' => 'PostCharge',
            'debitedAccount' => ['name' => 'Guest', 'number' => '1', 'type' => 'Receivables'],
            'creditedAccount' => ['name' => 'Accommodation', 'number' => '2', 'parentNumber' => 'R', 'type' => 'Revenues'],
            'amount' => ['amount' => 100, 'currency' => 'EUR'], 'receipt' => ['type' => 'Reservation', 'number' => 'R1'],
            'entryNumber' => 'E1', 'entryGroupNumber' => 'G1', 'reference' => 'F1', 'referenceType' => 'Guest',
        ]]]);

        $filter = new TransactionFilter('MUC', new \DateTimeImmutable('2026-03-01T00:00:00+00:00'), new \DateTimeImmutable('2026-03-31T00:00:00+00:00'), reference: 'F1');
        $transactions = $this->api->accounts()->exportDaily($filter);

        self::assertSame(TransactionCommand::PostCharge, $transactions[0]->command);
        self::assertSame(AccountType::Revenues, $transactions[0]->creditedAccount->type);
        self::assertSame(FolioType::Guest, $transactions[0]->referenceType);
        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/finance/v1/accounts/export-daily?propertyId=MUC&from=2026-03-01&to=2026-03-31&reference=F1', $this->lastUri());

        $this->respond(['aggregations' => [], 'total' => [
            'creditedAmount' => ['amount' => 1, 'currency' => 'EUR'], 'debitedAmount' => ['amount' => 1, 'currency' => 'EUR'], 'balance' => ['amount' => 0, 'currency' => 'EUR'],
        ]]);
        $aggregates = $this->api->accounts()->aggregate($filter);

        self::assertNull($aggregates->total->account);
        self::assertStringEndsWith('/finance/v1/accounts/aggregate?propertyId=MUC&from=2026-03-01T00:00:00+00:00&to=2026-03-31T00:00:00+00:00', $this->lastUri());
    }

    public function testChartOfAccountsAndSubAccounts(): void
    {
        $this->respond(['globalAccounts' => [[
            'accountNumber' => 'R', 'name' => 'Revenues', 'type' => 'Revenues', 'hasChildren' => true, 'isArchived' => false,
            'subAccounts' => [['accountNumber' => 'R-7', 'name' => 'Revenues 7%', 'type' => 'Revenues', 'parentNumber' => 'R', 'hasChildren' => false, 'isArchived' => false, 'vat' => ['type' => 'Reduced', 'percent' => 7]]],
        ]], 'guestAccounts' => [], 'externalAccounts' => [], 'bookingAccounts' => []]);

        $chart = $this->api->accounts()->chartOfAccounts('MUC', depth: 2);

        self::assertSame(VatType::Reduced, $chart->globalAccounts[0]->subAccounts[0]->vat?->type);
        self::assertStringEndsWith('/finance/v1/accounts/schema?propertyId=MUC&depth=2', $this->lastUri());
    }

    public function testTypes(): void
    {
        $this->respond(['paymentMethods' => ['Cash', 'SomethingNew']]);
        self::assertSame([PaymentMethod::Cash, PaymentMethod::Unknown], $this->api->types()->paymentMethods());

        $this->respond(['vatTypes' => [['type' => 'Normal', 'percent' => 19]]]);
        $vat = $this->api->types()->vatTypes('DE', new \DateTimeImmutable('2026-01-01'));

        self::assertSame(19.0, $vat[0]->percent);
        self::assertStringEndsWith('/finance/v1/types/vat?isoCountryCode=DE&atDate=2026-01-01', $this->lastUri());
    }
}
