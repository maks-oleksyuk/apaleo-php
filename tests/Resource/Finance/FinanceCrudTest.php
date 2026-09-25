<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\FinanceAccount;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\Payment;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\CreateFolioRefund;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\Refund;
use Oleksyuk\Apaleo\Tests\Support\Fixture;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;

/**
 * Get/update/delete/exists/count round-trips; responses are built from the DTO constructors by Fixture.
 *
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Finance')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class FinanceCrudTest extends FinanceTestCase
{
    public function testAccountGetMapsResponse(): void
    {
        $this->respond(Fixture::response(FinanceAccount::class));

        $account = $this->api->accounts()->get('MUC', '1000');

        self::assertInstanceOf(FinanceAccount::class, $account);
        self::assertSame('GET', $this->lastRequest()->getMethod());
    }

    public function testPaymentGetMapsResponse(): void
    {
        $this->respond(Fixture::response(Payment::class));

        $this->api->payments()->get('F1', 'P1');

        self::assertSame('GET', $this->lastRequest()->getMethod());
        self::assertStringContainsString('P1', $this->lastUri());
    }

    public function testRefundGetAndCreate(): void
    {
        $this->respond(Fixture::response(Refund::class));
        $this->api->refunds()->get('F1', 'R1');
        self::assertStringContainsString('R1', $this->lastUri());

        $this->respond(['id' => 'R2']);
        self::assertSame('R2', $this->api->refunds()->create('F1', Fixture::object(CreateFolioRefund::class), 'key-1'));
        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertSame('key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));
    }

    public function testRoutingGetUpdateAndDelete(): void
    {
        $this->respond(['id' => 'RT1', 'bookingId' => 'B1', 'propertyId' => 'MUC', 'destinationFolio' => ['id' => 'F1'], 'actions' => []]);
        self::assertSame('F1', $this->api->routings()->get('RT1')->destinationFolioId);

        $this->httpClient->addResponse(new Response(204));
        $this->api->routings()->update('RT1', new JsonPatch()->replace('/actions', []));
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->httpClient->addResponse(new Response(204));
        $this->api->routings()->delete('RT1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }

    public function testFolioExistsCountUpdateAndDelete(): void
    {
        $this->httpClient->addResponse(new Response(200));
        $this->httpClient->addResponse(new Response(404));
        self::assertTrue($this->api->folios()->exists('F1'));
        self::assertFalse($this->api->folios()->exists('F2'));
        self::assertSame('HEAD', $this->lastRequest()->getMethod());

        $this->respond(['count' => 7]);
        self::assertSame(7, $this->api->folios()->count());

        $this->httpClient->addResponse(new Response(204));
        $this->api->folios()->update('F1', new JsonPatch()->replace('/name', 'x'));
        self::assertSame('PATCH', $this->lastRequest()->getMethod());

        $this->httpClient->addResponse(new Response(204));
        $this->api->folios()->delete('F1');
        self::assertSame('DELETE', $this->lastRequest()->getMethod());
    }
}
