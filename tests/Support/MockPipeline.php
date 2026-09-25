<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Builds a RequestPipeline backed by a mock HTTP client and a fixed access token.
 *
 * @mixin TestCase
 */
trait MockPipeline
{
    protected MockClient $httpClient;

    protected function createPipeline(): RequestPipeline
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        return new RequestPipeline($this->httpClient, $factory, $factory, new FakeTokenProvider());
    }

    protected function lastRequest(): RequestInterface
    {
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);

        return $request;
    }

    /** @param array<array-key, mixed> $body */
    protected function respond(array $body, int $status = 200): void
    {
        $this->httpClient->addResponse(new Response($status, ['Content-Type' => 'application/json'], (string) json_encode($body)));
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
