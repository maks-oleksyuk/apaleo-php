<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Auth;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\ClientCredentialsTokenProvider;
use Oleksyuk\Apaleo\Auth\InMemoryTokenCache;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoServerException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 */
#[CoversClass(ClientCredentialsTokenProvider::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class ClientCredentialsTokenProviderTest extends TestCase
{
    private MockClient $httpClient;

    private InMemoryTokenCache $cache;

    private ClientCredentialsTokenProvider $provider;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $this->cache = new InMemoryTokenCache();
        $factory = new Psr17Factory();

        $this->provider = new ClientCredentialsTokenProvider(
            $this->httpClient,
            $factory,
            $factory,
            'client-id',
            'client-secret',
            $this->cache,
        );
    }

    public function testEmptyCredentialsAreRejectedUpFront(): void
    {
        $factory = new Psr17Factory();

        $this->expectException(\InvalidArgumentException::class);

        new ClientCredentialsTokenProvider($this->httpClient, $factory, $factory, '', 'client-secret');
    }

    public function testFetchesTokenAndCachesItOnColdCache(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'access_token' => 'fresh-token',
            'expires_in' => 3600,
        ])));

        $token = $this->provider->getToken();

        self::assertSame('fresh-token', $token->value);
        self::assertCount(1, $this->httpClient->getRequests());
        self::assertSame('fresh-token', $this->cache->get('apaleo_token_client-id')?->value);
    }

    public function testReturnsCachedTokenWithoutSendingARequest(): void
    {
        $this->cache->set('apaleo_token_client-id', new AccessToken('cached-token', new \DateTimeImmutable('+1 hour')));

        $token = $this->provider->getToken();

        self::assertSame('cached-token', $token->value);
        self::assertCount(0, $this->httpClient->getRequests());
    }

    public function testRefetchesWhenCachedTokenIsExpired(): void
    {
        $this->cache->set('apaleo_token_client-id', new AccessToken('stale-token', new \DateTimeImmutable('-1 hour')));
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'access_token' => 'renewed-token',
            'expires_in' => 3600,
        ])));

        $token = $this->provider->getToken();

        self::assertSame('renewed-token', $token->value);
        self::assertCount(1, $this->httpClient->getRequests());
    }

    public function testTokenIsReusedUntilThirtySecondsBeforeExpiry(): void
    {
        $clock = new MockClock('2026-09-22 12:00:00');
        $factory = new Psr17Factory();
        $provider = new ClientCredentialsTokenProvider($this->httpClient, $factory, $factory, 'client-id', 'client-secret', clock: $clock);
        foreach (['first', 'second'] as $value) {
            $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode(['access_token' => $value, 'expires_in' => 3600])));
        }

        self::assertSame('first', $provider->getToken()->value);

        $clock->sleep(3569);
        self::assertSame('first', $provider->getToken()->value, 'still 31s left');

        $clock->sleep(1);
        self::assertSame('second', $provider->getToken()->value, 'within the 30s safety margin');
    }

    public function testForceRefreshBypassesCacheEvenWhenTokenIsStillValid(): void
    {
        $this->cache->set('apaleo_token_client-id', new AccessToken('still-valid-token', new \DateTimeImmutable('+1 hour')));
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'access_token' => 'forced-new-token',
            'expires_in' => 3600,
        ])));

        $token = $this->provider->getToken(forceRefresh: true);

        self::assertSame('forced-new-token', $token->value);
        self::assertCount(1, $this->httpClient->getRequests());
        self::assertSame('forced-new-token', $this->cache->get('apaleo_token_client-id')?->value);
    }

    public function testSendsBasicAuthAndFormEncodedGrantType(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], (string) json_encode([
            'access_token' => 'x',
            'expires_in' => 60,
        ])));

        $this->provider->getToken();

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringContainsString('/connect/token', (string) $request->getUri());
        self::assertSame('Basic '.base64_encode('client-id:client-secret'), $request->getHeaderLine('Authorization'));
        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        self::assertSame('grant_type=client_credentials', (string) $request->getBody());
    }

    public function test4xxMapsToAuthException(): void
    {
        $this->httpClient->addResponse(new Response(400, ['Content-Type' => 'application/json'], (string) json_encode([
            'error' => 'invalid_client',
            'error_description' => 'Invalid client credentials',
        ])));

        $this->expectException(ApaleoAuthException::class);
        $this->expectExceptionMessage('Invalid client credentials');

        $this->provider->getToken();
    }

    public function test5xxMapsToServerException(): void
    {
        $this->httpClient->addResponse(new Response(503, ['Content-Type' => 'application/json'], '{}'));

        $this->expectException(ApaleoServerException::class);

        $this->provider->getToken();
    }

    public function testTransportFailureMapsToTransportException(): void
    {
        $factory = new Psr17Factory();
        $client = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class extends \RuntimeException implements ClientExceptionInterface {};
            }
        };
        $provider = new ClientCredentialsTokenProvider($client, $factory, $factory, 'client-id', 'client-secret');

        $this->expectException(ApaleoTransportException::class);

        $provider->getToken();
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
