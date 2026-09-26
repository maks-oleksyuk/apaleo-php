<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoClientException;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Exception\ApaleoRateLimitException;
use Oleksyuk\Apaleo\Exception\ApaleoServerException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Exception\ApaleoValidationException;
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\UpdateReservationRequest;
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
        $this->expectExceptionMessageMatches('/^Failed to encode request body as JSON: \S/');

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

    public function testEnumsAndDatesInTheBodyAreSentAsTheirApiValues(): void
    {
        $this->httpClient->addResponse(new Response(204));
        $patch = new JsonPatch()
            ->replace('/status', ReservationStatus::Confirmed)
            ->replace('/arrival', new \DateTimeImmutable('2026-10-01T15:00:00+02:00'))
        ;

        $this->pipeline->send(new UpdateReservationRequest('R1', $patch));

        self::assertSame(
            '[{"op":"replace","path":"\/status","value":"Confirmed"},{"op":"replace","path":"\/arrival","value":"2026-10-01T15:00:00+02:00"}]',
            (string) $this->lastRequest()->getBody(),
        );
    }

    public function testUnknownEnumObjectInTheBodyIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->pipeline->send(new UpdateReservationRequest('R1', new JsonPatch()->replace('/status', ReservationStatus::Unknown)));
        } finally {
            self::assertFalse($this->httpClient->getLastRequest());
        }
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

    public function testRequestGoesToTheBaseUriPlusEndpointWithItsQuery(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{"a":1,"b":2}'));

        $data = $this->pipeline->send($this->requestWithQuery(['propertyId' => 'MUC']));

        self::assertSame(['a' => 1, 'b' => 2], $data);
        self::assertSame('GET', $this->lastRequest()->getMethod());
        self::assertSame('https://api.apaleo.com/x?propertyId=MUC', (string) $this->lastRequest()->getUri());
    }

    public function testRequestWithoutQueryHasNoQuestionMark(): void
    {
        $this->httpClient->addResponse(new Response(204));

        $this->pipeline->send($this->requestWithQuery([]));

        self::assertSame('https://api.apaleo.com/x', (string) $this->lastRequest()->getUri());
    }

    public function testRetryAfter401CarriesTheRefreshedToken(): void
    {
        $this->httpClient->addResponse(new Response(401, ['Content-Type' => 'application/json'], '{}'));
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $this->pipeline->send($this->requestWithQuery([]));

        $requests = $this->httpClient->getRequests();
        self::assertCount(2, $requests);
        self::assertSame('Bearer fake-token', $requests[0]->getHeaderLine('Authorization'));
        self::assertSame('Bearer fresh-token', $requests[1]->getHeaderLine('Authorization'));
    }

    /** @param class-string<\Throwable> $exception */
    #[DataProvider('provideStatusMapsToItsExceptionCases')]
    public function testStatusMapsToItsException(int $status, string $exception): void
    {
        // 401 is retried once, so it needs a second response.
        $this->httpClient->addResponse(new Response($status, ['Content-Type' => 'application/json'], '{"detail":"d"}'));
        $this->httpClient->addResponse(new Response($status, ['Content-Type' => 'application/json'], '{"detail":"d"}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail("Expected {$exception}");
        } catch (\Throwable $throwable) {
            self::assertSame($throwable::class, $exception);
            self::assertSame($status, $throwable->getCode());
        }
    }

    /** @return iterable<string, array{int, class-string<\Throwable>}> */
    public static function provideStatusMapsToItsExceptionCases(): iterable
    {
        yield '400' => [400, ApaleoValidationException::class];

        yield '401' => [401, ApaleoAuthException::class];

        yield '402' => [402, ApaleoClientException::class];

        yield '403' => [403, ApaleoAuthException::class];

        yield '404' => [404, ApaleoNotFoundException::class];

        yield '409' => [409, ApaleoClientException::class];

        yield '421' => [421, ApaleoClientException::class];

        yield '422' => [422, ApaleoValidationException::class];

        yield '423' => [423, ApaleoClientException::class];

        yield '429' => [429, ApaleoRateLimitException::class];

        yield '499' => [499, ApaleoClientException::class];

        yield '500' => [500, ApaleoServerException::class];
    }

    public function test422WithMessagesUsesTheFirstOneAsTheMessage(): void
    {
        $this->httpClient->addResponse(new Response(422, ['Content-Type' => 'application/json'], '{"title":"t","messages":[1,"A: first.","B: second."]}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoValidationException');
        } catch (ApaleoValidationException $apaleoValidationException) {
            self::assertSame(['A: first.', 'B: second.'], $apaleoValidationException->messages);
            self::assertSame('A: first.', $apaleoValidationException->getMessage());
        }
    }

    public function testMessagesAreIgnoredOutsideValidationStatuses(): void
    {
        $this->httpClient->addResponse(new Response(409, ['Content-Type' => 'application/json'], '{"detail":"conflict","messages":["m"]}'));

        $this->expectExceptionMessage('conflict');

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function testDetailWinsOverTitle(): void
    {
        $this->httpClient->addResponse(new Response(409, ['Content-Type' => 'application/json'], '{"title":"Conflict","detail":"Unit is occupied"}'));

        $this->expectExceptionMessage('Unit is occupied');

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function testJsonErrorWithoutDetailGetsAGenericMessage(): void
    {
        $this->httpClient->addResponse(new Response(500, ['Content-Type' => 'application/json'], '{"foo":1}'));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoServerException');
        } catch (ApaleoServerException $apaleoServerException) {
            self::assertSame('Apaleo API error (HTTP 500)', $apaleoServerException->getMessage());
        }
    }

    public function testHtmlErrorBodyIsFlattenedIntoTheMessage(): void
    {
        $this->httpClient->addResponse(new Response(502, ['Content-Type' => 'text/html'], "<html>\n  <h1>Bad   Gateway</h1>\n</html>\n"));

        try {
            $this->pipeline->send($this->requestWithQuery([]));
            self::fail('Expected ApaleoServerException');
        } catch (ApaleoServerException $apaleoServerException) {
            self::assertSame('Apaleo API error (HTTP 502): Bad Gateway', $apaleoServerException->getMessage());
        }
    }

    public function testSendRawMapsA400ToAnException(): void
    {
        $this->httpClient->addResponse(new Response(400, ['Content-Type' => 'application/json'], '{"detail":"bad"}'));

        $this->expectException(ApaleoValidationException::class);

        $this->pipeline->sendRaw($this->requestWithQuery([]));
    }

    public function testMalformedSuccessBodyIsExcerptedIntoTheMessage(): void
    {
        $this->httpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], '<html>oops</html>'));

        $this->expectException(ApaleoUnexpectedResponseException::class);
        $this->expectExceptionMessage('Apaleo API returned a non-JSON or malformed body (HTTP 200): oops');

        $this->pipeline->send($this->requestWithQuery([]));
    }

    public function testTransportFailureKeepsTheClientMessage(): void
    {
        $client = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class('connection refused') extends \RuntimeException implements ClientExceptionInterface {};
            }
        };
        $factory = new Psr17Factory();
        $pipeline = new RequestPipeline($client, $factory, $factory, new FakeTokenProvider());

        $this->expectException(ApaleoTransportException::class);
        $this->expectExceptionMessage('Failed to reach Apaleo API: connection refused');

        $pipeline->send($this->requestWithQuery([]));
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
