<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\ApaleoClient;
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Tests\Support\FakeTokenProvider;
use Oleksyuk\Apaleo\Tests\Support\MockPipeline;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ApaleoClient::class)]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class ApaleoClientTest extends TestCase
{
    use MockPipeline;

    public function testEveryDomainAccessorReturnsItsResource(): void
    {
        $client = $this->client();

        foreach (new \ReflectionClass($client)->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $returnType = $method->getReturnType();
            if ($method->isStatic() || $method->getNumberOfParameters() > 0 || !$returnType instanceof \ReflectionNamedType || $returnType->isBuiltin()) {
                continue;
            }

            $resource = $returnType->getName();
            \assert(class_exists($resource));
            self::assertInstanceOf($resource, $method->invoke($client), $method->getName());
        }
    }

    public function testSendReturnsTheDecodedBody(): void
    {
        $client = $this->client();
        $this->respond(['ok' => true]);

        self::assertSame(['ok' => true], $client->send($this->request()));
        self::assertSame('/ping', $this->lastRequest()->getUri()->getPath());
    }

    public function testSendRawReturnsTheBodyAsIs(): void
    {
        $client = $this->client();
        $this->httpClient->addResponse(new Response(200, [], 'raw-bytes'));

        self::assertSame('raw-bytes', $client->sendRaw($this->request()));
    }

    public function testSendManyReturnsTheBodiesInOrder(): void
    {
        $client = $this->client();
        $this->respond(['n' => 1]);
        $this->respond(['n' => 2]);

        self::assertSame([['n' => 1], ['n' => 2]], $client->sendMany([$this->request(), $this->request()]));
    }

    public function testCreateDiscoversAnHttpClientAndFactories(): void
    {
        self::assertInstanceOf(ApaleoClient::class, ApaleoClient::create('client-id', 'client-secret'));
    }

    private function client(): ApaleoClient
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        return new ApaleoClient($this->httpClient, $factory, $factory, new FakeTokenProvider());
    }

    private function request(): Request
    {
        return new readonly class extends Request {
            public function method(): Method
            {
                return Method::GET;
            }

            public function endpoint(): string
            {
                return '/ping';
            }
        };
    }
}
