<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\Split;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateAccountPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateCustomPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreatePaymentLink;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Enum\PaymentAccountOwner;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Enum\PaymentType;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\CreatePaymentRefund;
use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\PaidCharge;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentFailureCode;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentStatus;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Finance')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class PaymentAndRefundResourceTest extends FinanceTestCase
{
    public function testListPaymentsMapsPendingLinkWithoutMethod(): void
    {
        $this->respond(['count' => 1, 'payments' => [[
            'id' => 'P1', 'type' => 'PaymentLink', 'status' => 'Failure', 'amount' => ['amount' => 50, 'currency' => 'EUR'],
            'paymentDate' => '2026-03-01T12:00:00Z', 'businessDate' => '2026-03-01', 'failureCode' => 'TimedOut',
            'url' => 'https://pay.example/abc', 'expiresAt' => '2026-03-02T12:00:00Z',
            'externalReference' => ['merchantReference' => 'M1', 'pspReference' => 'PSP1'],
            'actions' => [['action' => 'Cancel', 'isAllowed' => false, 'reasons' => [['code' => 'CancelNotAllowedForPaymentNotInStatusPending', 'message' => 'Not pending']]]],
        ]]]);

        $payments = $this->api->payments()->list('F1', [PaymentStatus::Failure, PaymentStatus::Pending], expand: ['actions']);

        self::assertSame(PaymentType::PaymentLink, $payments[0]->type);
        self::assertNull($payments[0]->method);
        self::assertSame(PaymentFailureCode::TimedOut, $payments[0]->failureCode);
        self::assertSame('PSP1', $payments[0]->externalReference?->pspReference);
        self::assertFalse($payments[0]->actions[0]->isAllowed);
        self::assertStringEndsWith('/finance/v1/folios/F1/payments?statusCodes=Failure,Pending&expand=actions', $this->lastUri());
    }

    public function testCreatePaymentPicksEndpointByType(): void
    {
        $this->respond(['id' => 'P1'], 201);
        $this->api->payments()->create('F1', new CreateCustomPayment(PaymentMethod::Cash, new MonetaryValue(10, 'EUR'), paidCharges: [new PaidCharge('C1', 10)]));
        self::assertStringEndsWith('/finance/v1/folios/F1/payments', $this->lastUri());
        self::assertSame(['method' => 'Cash', 'amount' => ['amount' => 10, 'currency' => 'EUR'], 'paidCharges' => [['chargeId' => 'C1', 'amount' => 10]]], $this->lastBody());

        $this->respond(['id' => 'P2'], 201);
        $this->api->payments()->create('F1', new CreateAccountPayment(new MonetaryValue(10, 'EUR'), PaymentAccountOwner::Booker), 'key-2');
        self::assertStringEndsWith('/finance/v1/folios/F1/payments/by-payment-account', $this->lastUri());
        self::assertSame('key-2', $this->lastRequest()->getHeaderLine('Idempotency-Key'));

        $this->respond(['id' => 'P3'], 201);
        $this->api->payments()->create('F1', new CreatePaymentLink(new \DateTimeImmutable('2026-03-02T12:00:00+00:00'), 'DE', new MonetaryValue(10, 'EUR'), payerEmail: 'guest@example.com'));
        self::assertStringEndsWith('/finance/v1/folios/F1/payments/by-link', $this->lastUri());
        self::assertSame(['expiresAt' => '2026-03-02T12:00:00+00:00', 'countryCode' => 'DE', 'amount' => ['amount' => 10, 'currency' => 'EUR'], 'payerEmail' => 'guest@example.com'], $this->lastBody());
    }

    public function testSplitAndCancelPayment(): void
    {
        $this->respond(['refundId' => 'RF1', 'firstPaymentId' => 'P5', 'secondPaymentId' => 'P6']);
        $result = $this->api->payments()->split('F1', 'P1', Split::byAmount(new MonetaryValue(20, 'EUR')));

        self::assertSame('RF1', $result->refundId);
        self::assertSame(['type' => 'ByAmount', 'amount' => ['amount' => 20, 'currency' => 'EUR']], $this->lastBody());
        self::assertStringEndsWith('/finance/v1/folio-actions/F1/payments/P1/split', $this->lastUri());

        $this->respond([], 204);
        $this->api->payments()->cancel('F1', 'P1');
        self::assertStringEndsWith('/finance/v1/folios/F1/payments/P1/cancel', $this->lastUri());
    }

    public function testRefunds(): void
    {
        $this->respond(['count' => 1, 'refunds' => [[
            'id' => 'RF1', 'status' => 'Success', 'method' => 'PspDebit', 'amount' => ['amount' => 10, 'currency' => 'EUR'],
            'refundDate' => '2026-03-03T09:00:00Z', 'businessDate' => '2026-03-03', 'sourcePaymentId' => 'P1', 'reason' => 'Goodwill',
        ]]]);

        $refunds = $this->api->refunds()->list('F1');

        self::assertSame(PaymentMethod::PspDebit, $refunds[0]->method);
        self::assertSame('P1', $refunds[0]->sourcePaymentId);

        $this->respond(['id' => 'RF2'], 201);
        $id = $this->api->refunds()->refundPayment('F1', 'P1', new CreatePaymentRefund(new MonetaryValue(5, 'EUR'), 'Goodwill'));

        self::assertSame('RF2', $id);
        self::assertStringEndsWith('/finance/v1/folios/F1/payments/P1/refunds', $this->lastUri());
        self::assertSame(['amount' => ['amount' => 5, 'currency' => 'EUR'], 'reason' => 'Goodwill'], $this->lastBody());
    }
}
