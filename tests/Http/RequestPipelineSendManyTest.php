<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Tests\Support\FakeTokenProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * @internal
 */
#[CoversClass(RequestPipeline::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
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

    public function testAsyncRequestHeadersCannotOverrideSdkHeaders(): void
    {
        $seenHeaders = [];
        $asyncClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$seenHeaders): MockResponse {
            $seenHeaders = $options['headers'];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline(new MockClient(), $factory, $factory, $this->fakeTokenProvider(), asyncHttpClient: $asyncClient);

        $pipeline->sendMany([new readonly class extends Request {
            public function method(): Method
            {
                return Method::POST;
            }

            public function endpoint(): string
            {
                return '/x';
            }

            public function headers(): array
            {
                return ['authorization' => 'Bearer evil', 'ACCEPT' => 'text/html', 'Idempotency-Key' => 'k1'];
            }
        }]);

        self::assertIsArray($seenHeaders);
        self::assertContains('Authorization: Bearer fake-token', $seenHeaders);
        self::assertContains('Idempotency-Key: k1', $seenHeaders);
        self::assertSame([], preg_grep('/evil|text\/html/', $seenHeaders));
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

    public function testAsyncPathSendsTheRequestBodyAsJson(): void
    {
        $seenBody = null;
        $asyncClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$seenBody): MockResponse {
            $seenBody = $options['body'] ?? null;

            return new MockResponse('{}', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]);
        });

        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline(new MockClient(), $factory, $factory, $this->fakeTokenProvider(), asyncHttpClient: $asyncClient);

        $pipeline->sendMany([new readonly class extends Request {
            public function method(): Method
            {
                return Method::POST;
            }

            public function endpoint(): string
            {
                return '/x';
            }

            public function body(): array
            {
                return ['a' => 1];
            }
        }]);

        self::assertSame('{"a":1}', $seenBody);
    }

    #[DataProvider('provideAsyncTransportErrorsSurfaceAsTransportExceptionsCases')]
    public function testAsyncTransportErrorsSurfaceAsTransportExceptions(string $failingStage): void
    {
        $response = new readonly class($failingStage) implements ResponseInterface {
            public function __construct(private string $failingStage) {}

            public function getStatusCode(): int
            {
                $this->failAt('status');

                return 200;
            }

            public function getHeaders(bool $throw = true): array
            {
                $this->failAt('headers');

                return [];
            }

            public function getContent(bool $throw = true): string
            {
                $this->failAt('content');

                return '{}';
            }

            /** @return array<string, mixed> */
            public function toArray(bool $throw = true): array
            {
                return [];
            }

            public function cancel(): void {}

            public function getInfo(?string $type = null): mixed
            {
                return null;
            }

            private function failAt(string $stage): void
            {
                if ($stage === $this->failingStage) {
                    throw new \RuntimeException('connection reset');
                }
            }
        };
        $asyncClient = new readonly class($response) implements HttpClientInterface {
            public function __construct(private ResponseInterface $response) {}

            /** @param array<string, mixed> $options */
            public function request(string $method, string $url, array $options = []): ResponseInterface
            {
                return $this->response;
            }

            public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
            {
                throw new \LogicException('not used');
            }

            /** @param array<string, mixed> $options */
            public function withOptions(array $options): static
            {
                return $this;
            }
        };

        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline(new MockClient(), $factory, $factory, $this->fakeTokenProvider(), asyncHttpClient: $asyncClient);

        $this->expectException(ApaleoTransportException::class);
        $this->expectExceptionMessage('connection reset');

        $pipeline->sendMany([$this->requestTo('/one')]);
    }

    /** @return iterable<string, array{string}> */
    public static function provideAsyncTransportErrorsSurfaceAsTransportExceptionsCases(): iterable
    {
        yield 'reading the status' => ['status'];

        yield 'reading the body' => ['content'];

        yield 'reading the headers' => ['headers'];
    }

    private function fakeTokenProvider(): TokenProvider
    {
        return new FakeTokenProvider();
    }

    /**
     * @param array<string, mixed> $query
     */
    private function requestTo(string $endpoint, array $query = []): Request
    {
        return new readonly class($endpoint, $query) extends Request {
            /**
             * @param array<string, mixed> $query
             */
            public function __construct(private string $endpoint, private array $query) {}

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
