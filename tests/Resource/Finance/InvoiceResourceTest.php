<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\CreateInvoiceAction;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\CreateInvoiceWarningType;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceAction;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceCancellationReason;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceStatus;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceType;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\InvoiceFilter;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Finance')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class InvoiceResourceTest extends FinanceTestCase
{
    public function testGetInvoiceMapsDocument(): void
    {
        $this->respond($this->documentFixture() + [
            'id' => 'I1', 'number' => 'MUC-00001', 'type' => 'Initial', 'status' => 'Unpaid', 'paymentSettled' => false,
            'created' => '2026-03-04T10:00:00Z', 'allowedActions' => ['Cancel', 'MarkAsPaid'],
        ]);

        $invoice = $this->api->invoices()->get('I1');

        self::assertSame(InvoiceType::Initial, $invoice->type);
        self::assertSame(InvoiceStatus::Unpaid, $invoice->status);
        self::assertSame('Jane Doe', $invoice->to->name);
        self::assertSame('Hotel GmbH', $invoice->from->name);
        self::assertSame('Munich', $invoice->from->address?->city);
        self::assertSame('DE123', $invoice->commercialInformation->taxId);
        self::assertSame('Breakfast', $invoice->lineItems[0]->includedLineItems[0]->description);
        self::assertSame(214.0, $invoice->subTotal->amount);
        self::assertSame('Bargeld', $invoice->payments[0]->methodName);
        self::assertSame(214.0, $invoice->total->amount);
        self::assertSame('101', $invoice->stayInfo?->roomNumber);
        self::assertSame([InvoiceAction::Cancel, InvoiceAction::MarkAsPaid], $invoice->allowedActions);
    }

    public function testPreviewReportsWarning(): void
    {
        $this->respond($this->documentFixture() + [
            'createInvoiceAction' => 'CannotCreateInvoice',
            'createInvoiceWarning' => ['type' => 'NotAllChargesPosted', 'message' => 'Post all charges first'],
        ]);

        $preview = $this->api->invoices()->preview('F1');

        self::assertSame(CreateInvoiceAction::CannotCreateInvoice, $preview->createInvoiceAction);
        self::assertSame(CreateInvoiceWarningType::NotAllChargesPosted, $preview->createInvoiceWarning?->type);
        self::assertStringEndsWith('/finance/v1/invoices/preview?folioId=F1', $this->lastUri());
    }

    public function testListInvoicesSendsFilter(): void
    {
        $this->respond(['count' => 1, 'invoices' => [[
            'id' => 'I1', 'number' => 'MUC-00001', 'type' => 'Initial', 'languageCode' => 'de', 'folioId' => 'F1', 'propertyId' => 'MUC',
            'subTotal' => ['amount' => 214, 'currency' => 'EUR'], 'paymentSettled' => true, 'status' => 'FullyPaid', 'created' => '2026-03-04T10:00:00Z',
            'guestName' => 'Jane Doe',
        ]]]);

        $invoices = $this->api->invoices()->list(new InvoiceFilter(status: InvoiceStatus::FullyPaid, propertyIds: ['MUC'], dateFilter: ['gte_2026-03-01']), pageSize: 20);

        self::assertSame('Jane Doe', $invoices[0]->guestName);
        self::assertStringEndsWith('/finance/v1/invoices?status=FullyPaid&propertyIds=MUC&dateFilter=gte_2026-03-01&pageSize=20', $this->lastUri());
    }

    public function testPdfReturnsRawBytesAndAsksForPdf(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-1.7 fake'));

        self::assertSame('%PDF-1.7 fake', $this->api->invoices()->pdf('I1'));
        self::assertSame('application/pdf', $this->lastRequest()->getHeaderLine('Accept'));
        self::assertStringEndsWith('/finance/v1/invoices/I1/pdf', $this->lastUri());
    }

    public function testPdfStillMapsErrors(): void
    {
        $this->respond(['detail' => 'Invoice not found'], 404);

        $this->expectException(ApaleoNotFoundException::class);
        $this->expectExceptionMessage('Invoice not found');

        $this->api->invoices()->previewPdf('F404', 'en');
    }

    public function testCreatePayAndCancel(): void
    {
        $this->respond(['id' => 'I2'], 201);
        self::assertSame('I2', $this->api->invoices()->create('F1', 'de', 'key-3'));
        self::assertSame(['folioId' => 'F1', 'languageCode' => 'de'], $this->lastBody());

        $this->respond([], 204);
        $this->api->invoices()->markAsPaid('I2', PaymentMethod::BankTransfer, 'BT-1');
        self::assertStringEndsWith('/finance/v1/invoice-actions/I2/pay', $this->lastUri());
        self::assertSame(['paymentMethod' => 'BankTransfer', 'receipt' => 'BT-1'], $this->lastBody());

        $this->respond([], 204);
        $this->api->invoices()->cancel('I2', InvoiceCancellationReason::ChangeOfInvoiceRecipient);
        self::assertSame(['reasonCode' => 'ChangeOfInvoiceRecipient'], $this->lastBody());
    }

    /** @return array<string, mixed> */
    private function documentFixture(): array
    {
        return [
            'invoiceDate' => '2026-03-04',
            'folioId' => 'F1',
            'propertyId' => 'MUC',
            'propertyCountryCode' => 'DE',
            'languageCode' => 'de',
            'to' => ['name' => 'Jane Doe', 'address' => ['city' => 'Berlin']],
            'from' => ['name' => 'Hotel GmbH', 'address' => ['addressLine1' => 'Main 1', 'postalCode' => '80331', 'city' => 'Munich', 'countryCode' => 'DE']],
            'commercialInformation' => ['registerEntry' => 'HRB 1', 'taxId' => 'DE123'],
            'lineItems' => [
                'lineItems' => [[
                    'date' => '2026-03-01', 'description' => 'Night', 'price' => ['amount' => 214, 'currency' => 'EUR'], 'isNoShowFee' => false,
                    'includedLineItems' => [['description' => 'Breakfast', 'price' => ['amount' => 14, 'currency' => 'EUR']]],
                ]],
                'subTotal' => ['amount' => 214, 'currency' => 'EUR'],
            ],
            'payments' => [['id' => 'P1', 'method' => 'Cash', 'methodName' => 'Bargeld', 'amount' => ['amount' => 214, 'currency' => 'EUR']]],
            'taxDetails' => [['vatType' => 'Reduced', 'vatPercent' => 7, 'net' => ['amount' => 200, 'currency' => 'EUR'], 'tax' => ['amount' => 14, 'currency' => 'EUR']]],
            'total' => ['amount' => 214, 'currency' => 'EUR'],
            'stayInfo' => ['guestName' => 'Jane Doe', 'arrivalDate' => '2026-03-01', 'departureDate' => '2026-03-03', 'reservationId' => 'R1', 'roomNumber' => '101'],
        ];
    }
}
