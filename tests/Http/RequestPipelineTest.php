<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Exception\ApaleoRateLimitException;
use Oleksyuk\Apaleo\Exception\ApaleoServerException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Exception\ApaleoValidationException;
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Tests\Support\FakeTokenProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(RequestPipeline::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class RequestPipelineTest extends TestCase
{
    private MockClient $httpClient;

    private RequestPipeline $pipeline;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        $tokenProvider = new FakeTokenProvider();

        $this->pipeline = new RequestPipeline($this->httpClient, $factory, $factory, $tokenProvider);
    }

    public function testBoolQueryParametersAreSentAsTrueFalseStrings(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $this->pipeline->send($this->requestWithQuery(['includeArchived' => false, 'isOccupied' => true]));

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('includeArchived=false', $uri);
        self::assertStringContainsString('isOccupied=true', $uri);
    }

    public function test401MapsToAuthException(): void
    {
        // Retried once with a forced token refresh; still 401 both times.
        $this->httpClient->addResponse(new Response(401, ['Content-Type' => 'application/json'], '{"detail":"nope"}'));
        $this->httpClient->addResponse(new Response(401, ['Content-Type' => 'application/json'], '{"detail":"nope"}'));

        $this->expectException(ApaleoAuthException::class);

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function test401RetriesOnceWithForcedTokenRefreshThenSucceeds(): void
    {
        $this->httpClient->addResponse(new Response(401, ['Content-Type' => 'application/json'], '{"detail":"stale token"}'));
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}'));

        $data = $this->pipeline->send($this->requestWithQuery([]));

        self::assertSame(['ok' => true], $data);
    }

    public function test429MapsToRateLimitExceptionWithRetryAfter(): void
    {
        $this->httpClient->addResponse(new Response(429, ['Content-Type' => 'application/json', 'Retry-After' => '30'], '{}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoRateLimitException');
        } catch (ApaleoRateLimitException $apaleoRateLimitException) {
            self::assertSame(30, $apaleoRateLimitException->retryAfterSeconds);
        }
    }

    public function test429ParsesHttpDateRetryAfter(): void
    {
        $httpDate = new \DateTimeImmutable('+2 minutes')->format('D, d M Y H:i:s \G\M\T');
        $this->httpClient->addResponse(new Response(429, ['Content-Type' => 'application/json', 'Retry-After' => $httpDate], '{}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoRateLimitException');
        } catch (ApaleoRateLimitException $apaleoRateLimitException) {
            self::assertGreaterThan(0, $apaleoRateLimitException->retryAfterSeconds);
            self::assertLessThanOrEqual(120, $apaleoRateLimitException->retryAfterSeconds);
        }
    }

    public function testBaseUriTrailingSlashIsStripped(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $factory = new Psr17Factory();
        $tokenProvider = new FakeTokenProvider();
        $pipeline = new RequestPipeline($this->httpClient, $factory, $factory, $tokenProvider, 'https://api.apaleo.com/');

        $pipeline->send($this->requestWithQuery([]));

        self::assertStringNotContainsString('.com//', (string) $this->lastRequest()->getUri());
    }

    public function test5xxMapsToServerException(): void
    {
        $this->httpClient->addResponse(new Response(503, ['Content-Type' => 'application/json'], '{}'));

        $this->expectException(ApaleoServerException::class);
        $this->expectExceptionCode(503);

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function testHtmlBodyOn5xxStillMapsToServerException(): void
    {
        $this->httpClient->addResponse(new Response(502, ['Content-Type' => 'text/html'], '<html><body><h1>502 Bad Gateway</h1></body></html>'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoServerException.');
        } catch (ApaleoServerException $apaleoServerException) {
            self::assertSame(502, $apaleoServerException->statusCode);
            self::assertStringContainsString('502 Bad Gateway', $apaleoServerException->getMessage());
        }
    }

    public function testNonJsonBodyOn4xxStillMapsByStatus(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'text/plain'], 'Not Found'));

        $this->expectException(ApaleoNotFoundException::class);

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function testUnknownEnumPlaceholderInQueryIsRejectedWithoutSendingARequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->pipeline->send($this->requestWithQuery(['status' => 'Confirmed,__unknown__']));
        } finally {
            self::assertFalse($this->httpClient->getLastRequest());
        }
    }

    public function testUnmappedEnumPlaceholderNestedInBodyIsRejected(): void
    {
        $request = new readonly class extends Request {
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
                return ['guests' => [['gender' => '__unmapped__']]];
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        $this->pipeline->send($request);
    }

    public function testInvalidJsonBodyOnSuccessThrowsUnexpectedResponseException(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'text/html'], '<html>not json</html>'));

        $this->expectException(ApaleoUnexpectedResponseException::class);

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function testEmptyBodyOn204IsTreatedAsEmptyArray(): void
    {
        $this->httpClient->addResponse(new Response(204));

        self::assertSame([], $this->pipeline->send($this->requestWithQuery([])));
    }

    public function testRequestHeadersCannotOverrideSdkHeaders(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $this->pipeline->send(new readonly class extends Request {
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
        });

        $sent = $this->lastRequest();
        self::assertSame('Bearer fake-token', $sent->getHeaderLine('Authorization'));
        self::assertSame('application/json', $sent->getHeaderLine('Accept'));
        self::assertSame('k1', $sent->getHeaderLine('Idempotency-Key'));
    }

    public function testUnencodableBodyIsAnInvalidArgumentNotAResponseError(): void
    {
        $request = new readonly class extends Request {
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
                return ['name' => "\xB1"];
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        $this->pipeline->send($request);
    }

    public function testFloatNoiseIsStrippedFromTheBody(): void
    {
        $this->httpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], '{}'));
        $request = new readonly class extends Request {
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
                return ['amount' => ['amount' => 0.1 + 0.2, 'currency' => 'EUR'], 'percent' => 12.345];
            }
        };

        $this->pipeline->send($request);

        self::assertSame('{"amount":{"amount":0.3,"currency":"EUR"},"percent":12.345}', (string) $this->lastRequest()->getBody());
    }

    public function testTransportFailureMapsToTransportException(): void
    {
        $client = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class extends \RuntimeException implements ClientExceptionInterface {};
            }
        };

        $factory = new Psr17Factory();
        $tokenProvider = new FakeTokenProvider();
        $pipeline = new RequestPipeline($client, $factory, $factory, $tokenProvider);

        $this->expectException(ApaleoTransportException::class);

        $pipeline->send($this->requestWithQuery([]));
    }

    public function testSendRawReturnsTheBodyUntouched(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-1.7 binary'));

        self::assertSame('%PDF-1.7 binary', $this->pipeline->sendRaw($this->requestWithQuery([])));
    }

    public function testSendRawStillMapsErrorsToExceptions(): void
    {
        $this->httpClient->addResponse(new Response(404, ['Content-Type' => 'application/json'], '{"detail":"no invoice"}'));

        $this->expectException(ApaleoNotFoundException::class);
        $this->expectExceptionMessage('no invoice');

        $this->pipeline->sendRaw($this->requestWithQuery([]));
    }

    public function testNonJsonErrorBodyIsExcerptedIntoTheMessage(): void
    {
        $this->httpClient->addResponse(new Response(500, ['Content-Type' => 'text/plain'], 'upstream exploded'));

        $this->expectException(ApaleoServerException::class);
        $this->expectExceptionMessage('Apaleo API error (HTTP 500): upstream exploded');

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function test400WithMessagesMapsToValidationExceptionCarryingThem(): void
    {
        $this->httpClient->addResponse(new Response(400, ['Content-Type' => 'application/json'], '{"messages":["Code: The Code field is required.",5]}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoValidationException');
        } catch (ApaleoValidationException $apaleoValidationException) {
            self::assertSame(['Code: The Code field is required.'], $apaleoValidationException->messages);
            self::assertSame('Code: The Code field is required.', $apaleoValidationException->getMessage());
        }
    }

    /** @param array<string, string> $headers */
    #[DataProvider('provide429WithoutUsableRetryAfterHasNoDelayCases')]
    public function test429WithoutUsableRetryAfterHasNoDelay(array $headers): void
    {
        $this->httpClient->addResponse(new Response(429, ['Content-Type' => 'application/json', ...$headers], '{}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoRateLimitException');
        } catch (ApaleoRateLimitException $apaleoRateLimitException) {
            self::assertNull($apaleoRateLimitException->retryAfterSeconds);
        }
    }

    /** @return iterable<string, array{array<string, string>}> */
    public static function provide429WithoutUsableRetryAfterHasNoDelayCases(): iterable
    {
        yield 'absent' => [[]];

        yield 'garbage' => [['Retry-After' => 'soon']];
    }

    /** @param array<string, mixed> $query */
    private function requestWithQuery(array $query): Request
    {
        return new readonly class($query) extends Request {
            /**
             * @param array<string, mixed> $query
             */
            public function __construct(private array $query) {}

            public function method(): Method
            {
                return Method::GET;
            }

            public function endpoint(): string
            {
                return '/x';
            }

            public function query(): array
            {
                return $this->query;
            }
        };
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }
}
