<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Resource\Finance;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Auth\AccessToken;
use Oleksyuk\Apaleo\Auth\TokenProvider;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\FinanceResource;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

abstract class FinanceTestCase extends TestCase
{
    protected MockClient $httpClient;

    protected FinanceResource $api;

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

        $this->api = new FinanceResource(new RequestPipeline($this->httpClient, $factory, $factory, $tokenProvider));
    }

    /** @param array<array-key, mixed> $body */
    protected function respond(array $body, int $status = 200): void
    {
        $this->httpClient->addResponse(new Response($status, ['Content-Type' => 'application/json'], (string) json_encode($body)));
    }

    protected function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }

    protected function lastUri(): string
    {
        return urldecode((string) $this->lastRequest()->getUri());
    }

    /** @return array<array-key, mixed> */
    protected function lastBody(): array
    {
        $body = json_decode((string) $this->lastRequest()->getBody(), true);
        self::assertIsArray($body);

        return $body;
    }
}
