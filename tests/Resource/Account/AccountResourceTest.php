<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Account;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Account\AccountResource;
use Oleksyuk\Apaleo\Resource\Account\DTO\CreateAccount;
use Oleksyuk\Apaleo\Resource\Account\DTO\ReplaceAccount;
use Oleksyuk\Apaleo\Resource\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class AccountResourceTest extends TestCase
{
    private MockClient $httpClient;

    private AccountResource $account;

    /** @var array<string, mixed> */
    private array $accountFixture;

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
        $this->account = new AccountResource($pipeline);

        $this->accountFixture = [
            'code' => 'DEMO',
            'name' => 'Demo Account',
            'description' => 'This is the demo account',
            'defaultLanguage' => 'en',
            'logoUrl' => 'logo.png',
            'location' => ['addressLine1' => 'My Street 1', 'postalCode' => '12345', 'city' => 'MyCity', 'countryCode' => 'GB'],
            'type' => 'Trial',
            'additionallySupportedCountries' => ['ES', 'FR'],
        ];
    }

    public function testGetCurrentMapsResponseToDto(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($this->accountFixture)));

        $account = $this->account->getCurrent();

        self::assertSame('DEMO', $account->code);
        self::assertSame(AccountType::Trial, $account->type);
        self::assertSame('MyCity', $account->location?->city);
        self::assertSame(['ES', 'FR'], $account->additionallySupportedCountries);
        self::assertSame('GET', $this->lastRequest()->getMethod());
        self::assertStringEndsWith('/account/v1/accounts/current', (string) $this->lastRequest()->getUri());
    }

    public function testUnknownTypeFallsBackWithoutThrowing(): void
    {
        $fixture = $this->accountFixture;
        $fixture['type'] = 'SomeFutureType';
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode($fixture)));

        $account = $this->account->getCurrent();

        self::assertSame(AccountType::Unknown, $account->type);
    }

    public function testReplaceCurrentSendsBody(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->account->replaceCurrent(new ReplaceAccount(
            name: 'Demo Account',
            description: 'Updated',
            location: new Address('My Street 1', null, '12345', 'MyCity', null, 'GB'),
            additionallySupportedCountries: ['ES'],
        ));

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame(
            ['name' => 'Demo Account', 'description' => 'Updated', 'location' => ['addressLine1' => 'My Street 1', 'postalCode' => '12345', 'city' => 'MyCity', 'countryCode' => 'GB'], 'additionallySupportedCountries' => ['ES']],
            json_decode((string) $request->getBody(), true),
        );
    }

    public function testListMapsWrappedResponseAndSendsFilter(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'count' => 1,
            'accounts' => [['code' => 'DEMO', 'name' => 'Demo Account', 'description' => null, 'type' => 'Trial']],
        ])));

        $result = $this->account->list(['DEMO', 'OTHER']);

        self::assertSame(1, $result->totalCount);
        self::assertSame('DEMO', $result[0]->code);
        self::assertStringContainsString('accountCodes=DEMO%2COTHER', (string) $this->lastRequest()->getUri());
    }

    public function testListHandlesEmpty204Response(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $result = $this->account->list();
        self::assertCount(0, $result);
        self::assertSame(0, $result->totalCount);
    }

    public function testCreateReturnsCreatedCodeAndSendsIdempotencyKey(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], (string) json_encode(['code' => 'DEMO'])));

        $code = $this->account->create(
            new CreateAccount(name: 'Demo Account', defaultLanguage: 'en', type: AccountType::Trial),
            idempotencyKey: 'retry-key-1',
        );

        self::assertSame('DEMO', $code);
        self::assertSame('retry-key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));
    }

    public function testSuspendCurrentSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->account->suspendCurrent();

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringEndsWith('/account-actions/current/suspend', (string) $request->getUri());
    }

    public function testSetCurrentLiveSendsPutRequest(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->account->setCurrentLive();

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertStringEndsWith('/account-actions/current/set-live', (string) $request->getUri());
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
