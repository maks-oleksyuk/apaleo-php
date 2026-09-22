<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestPipelineSendManyTest extends TestCase
{
    public function testWithoutAsyncClientFallsBackToSendingOneByOne(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{"n":1}'));
        $httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{"n":2}'));

        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline($httpClient, $factory, $factory, $this->fakeTokenProvider());

        $results = $pipeline->sendMany([$this->requestTo('/one'), $this->requestTo('/two')]);

        self::assertSame([['n' => 1], ['n' => 2]], $results);
    }

    public function testEmptyRequestListReturnsEmptyArrayWithoutTouchingTheClient(): void
    {
        $httpClient = new MockClient();
        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline($httpClient, $factory, $factory, $this->fakeTokenProvider());

        self::assertSame([], $pipeline->sendMany([]));
        self::assertFalse($httpClient->getLastRequest());
    }

    public function testWithAsyncClientDispatchesEveryRequestAndPreservesOrder(): void
    {
        $asyncClient = new MockHttpClient([
            static fn (): MockResponse => new MockResponse('{"n":1}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            static fn (): MockResponse => new MockResponse('{"n":2}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            static fn (): MockResponse => new MockResponse('{"n":3}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
        ]);

        $syncClient = new MockClient();
        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline($syncClient, $factory, $factory, $this->fakeTokenProvider(), asyncHttpClient: $asyncClient);

        $results = $pipeline->sendMany([$this->requestTo('/one'), $this->requestTo('/two'), $this->requestTo('/three')]);

        self::assertSame([['n' => 1], ['n' => 2], ['n' => 3]], $results);
        self::assertSame(3, $asyncClient->getRequestsCount());
        self::assertFalse($syncClient->getLastRequest(), 'the PSR-18 client must be untouched when an async client is available');
    }

    public function testAsyncClientSendsAuthorizationHeaderAndQuery(): void
    {
        $seenMethod = null;
        $seenUrl = null;
        $seenOptions = null;
        $asyncClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$seenMethod, &$seenUrl, &$seenOptions): MockResponse {
            $seenMethod = $method;
            $seenUrl = $url;
            $seenOptions = $options;

            return new MockResponse('{}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]);
        });

        $syncClient = new MockClient();
        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline($syncClient, $factory, $factory, $this->fakeTokenProvider(), asyncHttpClient: $asyncClient);

        $pipeline->sendMany([$this->requestTo('/x', ['a' => 'b'])]);

        self::assertSame('GET', $seenMethod);
        self::assertStringContainsString('/x', (string) $seenUrl);
        self::assertIsArray($seenOptions);
        $headers = $seenOptions['headers'] ?? [];
        self::assertIsArray($headers);
        self::assertContains('Authorization: Bearer fake-token', $headers);
        self::assertSame(['a' => 'b'], $seenOptions['query'] ?? null);
    }

    public function test401OnAsyncPathRetriesOnlyThoseRequestsWithAFreshToken(): void
    {
        $calls = 0;
        $asyncClient = new MockHttpClient(static function () use (&$calls): MockResponse {
            ++$calls;

            // Call 1: request #1 fails with 401. Call 2: request #2 succeeds. Call 3: retry of #1 succeeds.
            return match ($calls) {
                1 => new MockResponse('{}', ['http_code' => 401]),
                2 => new MockResponse('{"n":2}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
                default => new MockResponse('{"n":1}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            };
        });

        $syncClient = new MockClient();
        $factory = new Psr17Factory();
        $tokenProvider = new class implements TokenProvider {
            public int $refreshCount = 0;

            public function getToken(bool $forceRefresh = false): AccessToken
            {
                if ($forceRefresh) {
                    ++$this->refreshCount;
                }

                return new AccessToken('token-'.($forceRefresh ? 'fresh' : 'stale'), new \DateTimeImmutable('+1 hour'));
            }
        };
        $pipeline = new RequestPipeline($syncClient, $factory, $factory, $tokenProvider, asyncHttpClient: $asyncClient);

        $results = $pipeline->sendMany([$this->requestTo('/one'), $this->requestTo('/two')]);

        self::assertSame([['n' => 1], ['n' => 2]], $results);
        self::assertSame(1, $tokenProvider->refreshCount);
        self::assertSame(3, $asyncClient->getRequestsCount());
    }

    public function testAsyncPathMapsErrorsTheSameAsTheSyncPath(): void
    {
        $asyncClient = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            '{"detail":"nope"}',
            ['http_code' => 401, 'response_headers' => ['Content-Type' => 'application/json']],
        ));

        $syncClient = new MockClient();
        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline($syncClient, $factory, $factory, $this->fakeTokenProvider(), asyncHttpClient: $asyncClient);

        $this->expectException(ApaleoAuthException::class);

        $pipeline->sendMany([$this->requestTo('/one')]);
    }

    private function fakeTokenProvider(): TokenProvider
    {
        return new class implements TokenProvider {
            public function getToken(bool $forceRefresh = false): AccessToken
            {
                return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
            }
        };
    }

    /**
     * @param array<string, mixed> $query
     */
    private function requestTo(string $endpoint, array $query = []): Request
    {
        return new class($endpoint, $query) extends Request {
            /**
             * @param array<string, mixed> $query
             */
            public function __construct(private readonly string $endpoint, private readonly array $query) {}

            public function method(): Method
            {
                return Method::GET;
            }

            public function endpoint(): string
            {
                return $this->endpoint;
            }

            public function query(): array
            {
                return $this->query;
            }
        };
    }
}
