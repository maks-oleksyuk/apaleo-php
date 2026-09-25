<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\Authorization;

use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\AuthorizationFilter;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\AuthorizationResource;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\PayerInteraction;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Booking')]
#[CoversNamespace('Oleksyuk\Apaleo\Resource\Shared')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class AuthorizationResourceTest extends TestCase
{
    use MockPipeline;

    private AuthorizationResource $authorizations;

    /** @var array<string, mixed> */
    private array $fullAuthorizationFixture;

    protected function setUp(): void
    {
        $pipeline = $this->createPipeline();
        $this->authorizations = new AuthorizationResource($pipeline);

        $this->fullAuthorizationFixture = [
            'id' => 'AUTH1',
            'target' => ['type' => 'Reservation', 'id' => 'XPGMSXGF-1', 'propertyId' => 'MUC'],
            'created' => '2026-09-18T11:31:09+02:00',
            'updated' => '2026-09-18T11:31:09+02:00',
            'amount' => ['amount' => 100.0, 'currency' => 'EUR'],
            'status' => 'Success',
            'payerInteraction' => 'Terminal',
        ];
    }

    public function testGetAuthorizationMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullAuthorizationFixture)));

        $authorization = $this->authorizations->get('AUTH1');

        self::assertSame('AUTH1', $authorization->id);
        self::assertSame(AuthorizationStatus::Success, $authorization->status);
        self::assertSame(PayerInteraction::Terminal, $authorization->payerInteraction);
        self::assertSame(AuthorizationTargetType::Reservation, $authorization->target->type);
        self::assertSame(100.0, $authorization->amount->amount);
    }

    public function testUnknownStatusFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fullAuthorizationFixture;
        $fixture['status'] = 'SomeFutureStatus';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        self::assertSame(AuthorizationStatus::Unknown, $this->authorizations->get('AUTH1')->status);
    }

    public function testNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found', 'title' => 'Authorization not found', 'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->authorizations->get('MISSING');
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'authorizations' => [$this->fullAuthorizationFixture],
        ])));

        $result = $this->authorizations->list(new AuthorizationFilter(status: [AuthorizationStatus::Success]), pageSize: 10);

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('status=Success', (string) $request->getUri());
        self::assertStringContainsString('pageSize=10', (string) $request->getUri());
    }

    public function testCreateByAuthorizationReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'AUTH1'])));

        $id = $this->authorizations->createByAuthorization(
            new AuthorizationTarget(AuthorizationTargetType::Reservation, 'XPGMSXGF-1', 'MUC'),
            new MonetaryValue(100.0, 'EUR'),
            'txn-ref-123',
        );

        self::assertSame('AUTH1', $id);

        /** @var array{transactionReference: string} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame('txn-ref-123', $body['transactionReference']);
    }

    public function testCreateByLinkReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'AUTH1'])));

        $id = $this->authorizations->createByLink(
            new AuthorizationTarget(AuthorizationTargetType::Booking, 'XPGMSXGF', 'MUC'),
            new MonetaryValue(100.0, 'EUR'),
            'DE',
            new \DateTimeImmutable('2026-09-25T00:00:00+02:00'),
        );

        self::assertSame('AUTH1', $id);

        $request = $this->lastRequest();
        self::assertStringContainsString('/authorizations/by-link', (string) $request->getUri());

        /** @var array{countryCode: string} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('DE', $body['countryCode']);
    }

    public function testCreateByPaymentAccountReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'AUTH1'])));

        $id = $this->authorizations->createByPaymentAccount(
            new AuthorizationTarget(AuthorizationTargetType::Reservation, 'XPGMSXGF-1', 'MUC'),
            new MonetaryValue(100.0, 'EUR'),
            paymentAccountId: 'PA1',
        );

        self::assertSame('AUTH1', $id);

        /** @var array{paymentAccountId: string} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame('PA1', $body['paymentAccountId']);
    }

    public function testCreateByTerminalReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'AUTH1'])));

        $id = $this->authorizations->createByTerminal(
            new AuthorizationTarget(AuthorizationTargetType::Reservation, 'XPGMSXGF-1', 'MUC'),
            new MonetaryValue(100.0, 'EUR'),
            'TERM1',
        );

        self::assertSame('AUTH1', $id);

        /** @var array{terminalId: string} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame('TERM1', $body['terminalId']);
    }

    public function testCancelSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->authorizations->cancel('AUTH1');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/authorization-actions/AUTH1/cancel', (string) $request->getUri());
    }

    public function testExpirePaymentLinkSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->authorizations->expirePaymentLink('AUTH1');

        self::assertStringContainsString('/authorization-actions/AUTH1/expire-payment-link', (string) $this->lastRequest()->getUri());
    }

    public function testRefreshSendsAmountBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->authorizations->refresh('AUTH1', new MonetaryValue(50.0, 'EUR'));

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/authorization-actions/AUTH1/refresh', (string) $request->getUri());

        /** @var array{amount: array{amount: float, currency: string}} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame(50.0, (float) $body['amount']['amount']);
        self::assertSame('EUR', $body['amount']['currency']);
    }
}
