<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Booking\PaymentAccount;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountTarget;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountPayerInteraction;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum\PaymentAccountStatus;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\PaymentAccountFilter;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\PaymentAccountResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class PaymentAccountResourceTest extends TestCase
{
    private MockClient $httpClient;

    private PaymentAccountResource $paymentAccounts;

    /** @var array<string, mixed> */
    private array $fullPaymentAccountFixture;

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
        $this->paymentAccounts = new PaymentAccountResource($pipeline);

        $this->fullPaymentAccountFixture = [
            'id' => 'PA1',
            'target' => ['type' => 'Reservation', 'id' => 'XPGMSXGF-1'],
            'created' => '2026-09-18T11:31:09+02:00',
            'updated' => '2026-09-18T11:31:09+02:00',
            'status' => 'Success',
            'payerInteraction' => 'PciToken',
            'isVirtual' => false,
        ];
    }

    public function testGetPaymentAccountMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->fullPaymentAccountFixture)));

        $account = $this->paymentAccounts->get('PA1');

        self::assertSame('PA1', $account->id);
        self::assertSame(PaymentAccountStatus::Success, $account->status);
        self::assertSame(PaymentAccountPayerInteraction::PciToken, $account->payerInteraction);
        self::assertSame(AuthorizationTargetType::Reservation, $account->target->type);
    }

    public function testUnknownStatusFallsBackWithoutThrowing(): void
    {
        $fixture = $this->fullPaymentAccountFixture;
        $fixture['status'] = 'SomeFutureStatus';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        self::assertSame(PaymentAccountStatus::Unknown, $this->paymentAccounts->get('PA1')->status);
    }

    public function testNotFoundMapsToApaleoNotFoundException(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], (string) json_encode([
            'type' => 'urn:apaleo:not-found', 'title' => 'Payment account not found', 'status' => 404,
        ])));

        $this->expectException(ApaleoNotFoundException::class);

        $this->paymentAccounts->get('MISSING');
    }

    public function testListMapsWrappedResponse(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'paymentAccounts' => [$this->fullPaymentAccountFixture],
        ])));

        $result = $this->paymentAccounts->list(new PaymentAccountFilter(status: [PaymentAccountStatus::Success]), pageSize: 10);

        self::assertCount(1, $result);
        self::assertSame(1, $result->totalCount);

        $request = $this->lastRequest();
        self::assertStringContainsString('status=Success', (string) $request->getUri());
        self::assertStringContainsString('pageSize=10', (string) $request->getUri());
    }

    public function testCreateByAuthorizationReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'PA1'])));

        $id = $this->paymentAccounts->createByAuthorization(
            new PaymentAccountTarget(AuthorizationTargetType::Reservation, 'XPGMSXGF-1'),
            'txn-ref-123',
        );

        self::assertSame('PA1', $id);

        /** @var array{transactionReference: string} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame('txn-ref-123', $body['transactionReference']);
    }

    public function testCreateByLinkReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'PA1'])));

        $id = $this->paymentAccounts->createByLink(
            new PaymentAccountTarget(AuthorizationTargetType::Booking, 'XPGMSXGF'),
            'MUC',
            'DE',
            new \DateTimeImmutable('2026-09-25T00:00:00+02:00'),
        );

        self::assertSame('PA1', $id);

        $request = $this->lastRequest();
        self::assertStringContainsString('/payment-accounts/by-link', (string) $request->getUri());

        /** @var array{propertyId: string, countryCode: string} $body */
        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('MUC', $body['propertyId']);
        self::assertSame('DE', $body['countryCode']);
    }

    public function testCreateByStoredPaymentMethodReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'PA1'])));

        $id = $this->paymentAccounts->createByStoredPaymentMethod(
            new PaymentAccountTarget(AuthorizationTargetType::Reservation, 'XPGMSXGF-1'),
            'guest-ref-1',
            'LATEST',
        );

        self::assertSame('PA1', $id);

        /** @var array{payerReference: string, storedPaymentMethodId: string} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame('guest-ref-1', $body['payerReference']);
        self::assertSame('LATEST', $body['storedPaymentMethodId']);
    }

    public function testCreateByTerminalReturnsCreatedId(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['id' => 'PA1'])));

        $id = $this->paymentAccounts->createByTerminal(
            new PaymentAccountTarget(AuthorizationTargetType::Reservation, 'XPGMSXGF-1'),
            'MUC',
            'TERM1',
        );

        self::assertSame('PA1', $id);

        /** @var array{terminalId: string} $body */
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertSame('TERM1', $body['terminalId']);
    }

    public function testCancelSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->paymentAccounts->cancel('PA1');

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/payment-account-actions/PA1/cancel', (string) $request->getUri());
    }

    public function testExpirePaymentLinkSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->paymentAccounts->expirePaymentLink('PA1');

        self::assertStringContainsString('/payment-account-actions/PA1/expire-payment-link', (string) $this->lastRequest()->getUri());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
