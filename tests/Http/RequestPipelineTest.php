<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Http;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Exception\ApaleoAuthException;
use Oleksyuk\Apaleo\Exception\ApaleoRateLimitException;
use Oleksyuk\Apaleo\Exception\ApaleoServerException;
use Oleksyuk\Apaleo\Exception\ApaleoTransportException;
use Oleksyuk\Apaleo\Exception\ApaleoUnexpectedResponseException;
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestPipelineTest extends TestCase
{
    private MockClient $httpClient;

    private RequestPipeline $pipeline;

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

    public function test5xxMapsToServerException(): void
    {
        $this->httpClient->addResponse(new Response(503, ['Content-Type' => 'application/json'], '{}'));

        $this->expectException(ApaleoServerException::class);

        $this->pipeline->send($this->requestWithQuery([]));
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

    public function testTransportFailureMapsToTransportException(): void
    {
        $client = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class extends \RuntimeException implements ClientExceptionInterface {};
            }
        };

        $factory = new Psr17Factory();
        $tokenProvider = new class implements TokenProvider {
            public function getToken(bool $forceRefresh = false): AccessToken
            {
                return new AccessToken('fake-token', new \DateTimeImmutable('+1 hour'));
            }
        };
        $pipeline = new RequestPipeline($client, $factory, $factory, $tokenProvider);

        $this->expectException(ApaleoTransportException::class);

        $pipeline->send($this->requestWithQuery([]));
    }

    /**
     * @param array<string, mixed> $query
     */
    private function requestWithQuery(array $query): Request
    {
        return new class($query) extends Request {
            protected Method $method = Method::GET;

            /**
             * @param array<string, mixed> $query
             */
            public function __construct(private readonly array $query) {}

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
